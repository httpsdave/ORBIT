<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentOrgController extends Controller
{
    /**
     * Display the student organizations management page (now users per college).
     */
    public function index()
    {
        // Get the admin role id to exclude admin users
        $adminRoleId = \App\Models\Role::where('slug', 'admin')->value('id');
        
        // Load colleges with their users, excluding admin accounts
        $colleges = College::with([
            'users' => function ($query) use ($adminRoleId) {
                $query->where('role_id', '!=', $adminRoleId);
            },
            'users.role', 
            'users.parentOrganization', 
            'users.subOrganizations.college'
        ])->get();
        
        // For selection modal, exclude admin accounts
        $users = User::with(['role', 'college', 'parentOrganization', 'subOrganizations.college'])
            ->where('role_id', '!=', $adminRoleId)
            ->get();
            
        return Inertia::render('Admin/StudentOrgs/Index', [
            'colleges' => $colleges,
            'users' => $users,
        ]);
    }

    /**
     * Display the specified student organization.
     */
    public function show(User $user)
    {
        $user->load(['college', 'role']);
        
        // Get organization details from latest approved applications
        // Latest approved List of Members form
        $latestMembersApp = \App\Models\OrganizationApplication::with('members')
            ->where('user_id', $user->id)
            ->where('status', 'Approved')
            ->where('form_type', 'LSPU-OSAS-SF-005')
            ->orderByDesc('created_at')
            ->first();
            
        // Latest approved List of Officers form
        $latestOfficersApp = \App\Models\OrganizationApplication::with('officers')
            ->where('user_id', $user->id)
            ->where('status', 'Approved')
            ->where('form_type', 'LSPU-OSAS-SF-007')
            ->orderByDesc('created_at')
            ->first();
            
        // Latest approved Student Organization Form (SF-001) or Renewal Form (SF-002)
        $latestOrgApp = \App\Models\OrganizationApplication::where('user_id', $user->id)
            ->where('status', 'Approved')
            ->whereIn('form_type', ['LSPU-OSAS-SF-001', 'LSPU-OSAS-SF-002'])
            ->orderByDesc('created_at')
            ->first();
        
        // Get adviser information from the latest available form (prefer members, fallback to officers)
        $adviser_name = $latestMembersApp->adviser_name ?? $latestOfficersApp->adviser_name ?? null;
        $second_adviser = $latestMembersApp->second_adviser ?? $latestOfficersApp->second_adviser ?? null;
        
        // Get president name from the latest approved organization/renewal form
        $president_name = $latestOrgApp->president_name ?? null;
        
        // Get members and officers from the latest approved forms
        $members = $latestMembersApp ? $latestMembersApp->members : collect();
        $officers = $latestOfficersApp ? $latestOfficersApp->officers : collect();
        
        // Prepare organization details
        $organizationDetails = [
            'adviser_name' => $adviser_name,
            'second_adviser' => $second_adviser,
            'president_name' => $president_name,
            'members_count' => $members->count(),
            'officers_count' => $officers->count(),
            'members' => $members,
            'officers' => $officers,
            'has_approved_data' => $latestMembersApp || $latestOfficersApp
        ];
        
        return Inertia::render('Admin/StudentOrgs/Show', [
            'studentOrg' => $user,
            'organizationDetails' => $organizationDetails
        ]);
    }

    /**
     * Assign one or more users to a college (set college_id).
     */
    public function assignUserToCollege(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'college_id' => 'required|exists:colleges,id',
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $user->college_id = $validated['college_id'];
                $user->save();
            }
        }

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'Users assigned to college successfully.');
    }

    /**
     * Remove a user from a college (unset college_id).
     */
    public function removeUserFromCollege(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->college_id = null;
        $user->save();

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'User removed from college successfully.');
    }

    /**
     * Toggle the status of a user (organization).
     */
    public function toggleStatus(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'Organization status updated successfully.');
    }

    /**
     * Assign a parent organization to a sub-organization.
     */
    public function assignParentOrganization(Request $request)
    {
        $validated = $request->validate([
            'sub_organization_id' => 'required|exists:users,id',
            'parent_organization_id' => 'required|exists:users,id',
        ]);

        $subOrg = User::findOrFail($validated['sub_organization_id']);
        $parentOrg = User::findOrFail($validated['parent_organization_id']);

        // Prevent circular relationships
        if ($this->wouldCreateCircularRelationship($subOrg, $parentOrg)) {
            return redirect()->route('admin.student-orgs.index')
                ->with('error', 'Cannot assign parent organization - this would create a circular relationship.');
        }

        // Prevent sub-organizations from becoming parent organizations
        if ($subOrg->subOrganizations()->exists()) {
            return redirect()->route('admin.student-orgs.index')
                ->with('error', 'Cannot assign parent to this organization - it already has sub-organizations and cannot be a sub-organization itself.');
        }

        // Prevent an organization from becoming a sub-organization if the proposed parent already has that organization as a parent (circular check)
        if ($parentOrg->parent_organization_id) {
            return redirect()->route('admin.student-orgs.index')
                ->with('error', 'Cannot assign this organization as parent - it is already a sub-organization itself.');
        }

        $subOrg->parent_organization_id = $validated['parent_organization_id'];
        $subOrg->save();

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'Parent organization assigned successfully.');
    }

    /**
     * Remove parent organization from a sub-organization.
     */
    public function removeParentOrganization(Request $request)
    {
        $validated = $request->validate([
            'sub_organization_id' => 'required|exists:users,id',
        ]);

        $subOrg = User::findOrFail($validated['sub_organization_id']);
        $subOrg->parent_organization_id = null;
        $subOrg->save();

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'Parent organization removed successfully.');
    }

    /**
     * Check if assigning a parent would create a circular relationship.
     */
    private function wouldCreateCircularRelationship($subOrg, $parentOrg)
    {
        // If the proposed parent is already a child of the sub-org, it would create a circle
        $currentParent = $parentOrg;
        while ($currentParent && $currentParent->parent_organization_id) {
            if ($currentParent->parent_organization_id === $subOrg->id) {
                return true;
            }
            $currentParent = $currentParent->parentOrganization;
        }
        return false;
    }

    /**
     * Export recognized student organizations as PDF.
     */
    public function exportRecognizedOrgsPdf(Request $request)
    {
        try {
            // Get academic year from request or use current
            $academicYear = $request->input('academic_year', '2024-2025');
            
            // Get all student organizations (exclude admin users)
            $adminRoleId = \App\Models\Role::where('slug', 'admin')->value('id');
            $users = User::with([
                'college', 
                'role', 
                'parentOrganization', 
                'subOrganizations.college'
            ])
                ->where('role_id', '!=', $adminRoleId)
                ->where('status', 'active')
                ->get();

            // Get organization details (president and adviser) from latest approved applications
            $organizationData = [];
            
            foreach ($users as $user) {
                // Get latest approved application for president name
                $latestOrgApp = \App\Models\OrganizationApplication::where('user_id', $user->id)
                    ->where('status', 'Approved')
                    ->whereIn('form_type', ['LSPU-OSAS-SF-001', 'LSPU-OSAS-SF-002'])
                    ->orderByDesc('created_at')
                    ->first();
                
                $president = $latestOrgApp->president_name ?? '';
                
                // Get adviser from latest commitment form
                $latestCommitment = \App\Models\OrganizationApplication::where('user_id', $user->id)
                    ->where('status', 'Approved')
                    ->where('form_type', 'LSPU-OSAS-SF-003')
                    ->orderByDesc('created_at')
                    ->first();
                
                $adviser = '';
                if ($latestCommitment && $latestCommitment->advisers) {
                    $advisersArray = is_string($latestCommitment->advisers) 
                        ? json_decode($latestCommitment->advisers, true) 
                        : $latestCommitment->advisers;
                    
                    if (isset($advisersArray[0])) {
                        $prefix = $advisersArray[0]['adviser_prefix'] ?? '';
                        $name = $advisersArray[0]['adviser_name'] ?? '';
                        $suffix = $advisersArray[0]['adviser_suffix'] ?? '';
                        
                        $adviser = trim($prefix . ' ' . $name . ' ' . $suffix);
                    }
                }
                
                $organizationData[$user->id] = [
                    'name' => $user->name,
                    'president' => $president,
                    'adviser' => $adviser,
                    'college_id' => $user->college_id,
                    'parent_organization_id' => $user->parent_organization_id,
                    'has_sub_orgs' => $user->subOrganizations->count() > 0,
                ];
            }

            // Categorize organizations
            $studentCouncils = [];
            $subOrganizations = [];
            $newRecognizedOrganizations = [];
            
            foreach ($organizationData as $userId => $org) {
                // Student Councils: College-affiliated organizations (have college_id)
                if ($org['college_id'] && !$org['parent_organization_id']) {
                    $studentCouncils[] = $org;
                }
                // Sub-Organizations: Organizations with parent_organization_id
                elseif ($org['parent_organization_id']) {
                    $subOrganizations[] = $org;
                }
                // New Recognized Organizations: Non-college affiliated, no parent
                elseif (!$org['college_id'] && !$org['parent_organization_id']) {
                    $newRecognizedOrganizations[] = $org;
                }
            }

            // Sort alphabetically by name
            usort($studentCouncils, fn($a, $b) => strcmp($a['name'], $b['name']));
            usort($subOrganizations, fn($a, $b) => strcmp($a['name'], $b['name']));
            usort($newRecognizedOrganizations, fn($a, $b) => strcmp($a['name'], $b['name']));

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.recognized_organizations', [
                'academicYear' => $academicYear,
                'studentCouncils' => $studentCouncils,
                'subOrganizations' => $subOrganizations,
                'newRecognizedOrganizations' => $newRecognizedOrganizations,
                'preparedBy' => $request->input('prepared_by'),
                'preparedByTitle' => $request->input('prepared_by_title'),
                'notedBy' => $request->input('noted_by'),
                'notedByTitle' => $request->input('noted_by_title'),
                'approvedBy' => $request->input('approved_by'),
                'approvedByTitle' => $request->input('approved_by_title'),
            ]);

            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Set PDF options for better rendering
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'dpi' => 96,
                'defaultFont' => 'sans-serif',
                'isPhpEnabled' => true,
            ]);

            // Check if action is 'view' to display inline, otherwise download
            $action = $request->input('action', 'download');
            $filename = 'Recognized_Student_Organizations_' . str_replace('-', '_', $academicYear) . '.pdf';

            if ($action === 'view') {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Error generating recognized organizations PDF: ' . $e->getMessage());
            return redirect()->route('admin.student-orgs.index')
                ->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export recognized student organizations as DOCX.
     */
    public function exportRecognizedOrgsDocx(Request $request)
    {
        try {
            // Get academic year from request or use current
            $academicYear = $request->input('academic_year', '2024-2025');
            
            // Get all student organizations (exclude admin users)
            $adminRoleId = \App\Models\Role::where('slug', 'admin')->value('id');
            $users = User::with([
                'college', 
                'role', 
                'parentOrganization', 
                'subOrganizations.college'
            ])
                ->where('role_id', '!=', $adminRoleId)
                ->where('status', 'active')
                ->get();

            // Get organization details (president and adviser) from latest approved applications
            $organizationData = [];
            
            foreach ($users as $user) {
                // Get latest approved application for president name
                $latestOrgApp = \App\Models\OrganizationApplication::where('user_id', $user->id)
                    ->where('status', 'Approved')
                    ->whereIn('form_type', ['LSPU-OSAS-SF-001', 'LSPU-OSAS-SF-002'])
                    ->orderByDesc('created_at')
                    ->first();
                
                $president = $latestOrgApp->president_name ?? '';
                
                // Get adviser from latest commitment form
                $latestCommitment = \App\Models\OrganizationApplication::where('user_id', $user->id)
                    ->where('status', 'Approved')
                    ->where('form_type', 'LSPU-OSAS-SF-003')
                    ->orderByDesc('created_at')
                    ->first();
                
                $adviser = '';
                if ($latestCommitment && $latestCommitment->advisers) {
                    $advisersArray = is_string($latestCommitment->advisers) 
                        ? json_decode($latestCommitment->advisers, true) 
                        : $latestCommitment->advisers;
                    
                    if (isset($advisersArray[0])) {
                        $prefix = $advisersArray[0]['adviser_prefix'] ?? '';
                        $name = $advisersArray[0]['adviser_name'] ?? '';
                        $suffix = $advisersArray[0]['adviser_suffix'] ?? '';
                        
                        $adviser = trim($prefix . ' ' . $name . ' ' . $suffix);
                    }
                }
                
                $organizationData[$user->id] = [
                    'name' => $user->name,
                    'president' => $president,
                    'adviser' => $adviser,
                    'college_id' => $user->college_id,
                    'parent_organization_id' => $user->parent_organization_id,
                    'has_sub_orgs' => $user->subOrganizations->count() > 0,
                ];
            }

            // Categorize organizations
            $studentCouncils = [];
            $subOrganizations = [];
            $newRecognizedOrganizations = [];
            
            foreach ($organizationData as $userId => $org) {
                // Student Councils: College-affiliated organizations (have college_id)
                if ($org['college_id'] && !$org['parent_organization_id']) {
                    $studentCouncils[] = $org;
                }
                // Sub-Organizations: Organizations with parent_organization_id
                elseif ($org['parent_organization_id']) {
                    $subOrganizations[] = $org;
                }
                // New Recognized Organizations: Non-college affiliated, no parent
                elseif (!$org['college_id'] && !$org['parent_organization_id']) {
                    $newRecognizedOrganizations[] = $org;
                }
            }

            // Sort alphabetically by name
            usort($studentCouncils, fn($a, $b) => strcmp($a['name'], $b['name']));
            usort($subOrganizations, fn($a, $b) => strcmp($a['name'], $b['name']));
            usort($newRecognizedOrganizations, fn($a, $b) => strcmp($a['name'], $b['name']));

            // Create DOCX using PHPWord
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            
            // Set document properties
            $properties = $phpWord->getDocInfo();
            $properties->setCreator(auth()->user()->name ?? 'OSAS Admin');
            $properties->setTitle('Recognized Student Organizations - ' . $academicYear);
            $properties->setSubject('List of Recognized Student Organizations');

            // Add section with A4 portrait
            $section = $phpWord->addSection([
                'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5),
                'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.0),
                'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.27),
                'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.27),
            ]);

            // Header
            $this->addDocxHeader($section);

            // Title
            $section->addText(
                'RECOGNIZED STUDENT ORGANIZATION',
                ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]
            );
            $section->addText(
                'ACADEMIC YEAR ' . strtoupper($academicYear),
                ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 200]
            );

            // Student Council Section
            if (count($studentCouncils) > 0) {
                $section->addText(
                    'STUDENT COUNCIL:',
                    ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'],
                    ['spaceAfter' => 100]
                );
                
                $this->addOrganizationTable($section, $studentCouncils);
            }

            // Sub-Organization Section
            if (count($subOrganizations) > 0) {
                $section->addText(
                    'SUB-ORGANIZATION:',
                    ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'],
                    ['spaceAfter' => 100, 'spaceBefore' => 200]
                );
                
                $this->addOrganizationTable($section, $subOrganizations);
            }

            // New Recognized Organizations Section (on new page if exists)
            if (count($newRecognizedOrganizations) > 0) {
                $section->addText(
                    'NEW RECOGNIZED ORGANIZATION:',
                    ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'],
                    ['spaceAfter' => 100, 'spaceBefore' => 200]
                );
                
                $this->addOrganizationTable($section, $newRecognizedOrganizations);
            }

            // Add signatures
            $this->addDocxSignatures($section, $request);

            // Save to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'recognized_orgs') . '.docx';
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($tempFile);

            // Return download response
            $filename = 'Recognized_Student_Organizations_' . str_replace('-', '_', $academicYear) . '.docx';
            
            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error generating recognized organizations DOCX: ' . $e->getMessage());
            return redirect()->route('admin.student-orgs.index')
                ->with('error', 'Error generating DOCX: ' . $e->getMessage());
        }
    }

    /**
     * Add header to DOCX document.
     */
    private function addDocxHeader($section)
    {
        $section->addText(
            'Republic of the Philippines',
            ['size' => 11, 'name' => 'Calibri'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'LAGUNA STATE POLYTECHNIC UNIVERSITY',
            ['size' => 12, 'name' => 'Old English Text MT', 'bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'Province of Laguna',
            ['size' => 11, 'name' => 'Calibri'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100]
        );
        $section->addText(
            'OFFICE OF THE STUDENT AFFAIRS AND SERVICES',
            ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 200]
        );
    }

    /**
     * Add organization table to DOCX.
     */
    private function addOrganizationTable($section, $organizations)
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'width' => 100 * 50,
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
        ]);

        // Header row
        $table->addRow();
        $table->addCell(500, ['valign' => 'center', 'bgColor' => '000000'])->addText(
            'No.',
            ['bold' => true, 'size' => 11, 'name' => 'Times New Roman', 'color' => 'FFFFFF'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $table->addCell(3700, ['valign' => 'center', 'bgColor' => '000000'])->addText(
            'Name of Organization',
            ['bold' => true, 'size' => 11, 'name' => 'Times New Roman', 'color' => 'FFFFFF'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $table->addCell(2500, ['valign' => 'center', 'bgColor' => '000000'])->addText(
            'Name of President',
            ['bold' => true, 'size' => 11, 'name' => 'Times New Roman', 'color' => 'FFFFFF'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $table->addCell(3300, ['valign' => 'center', 'bgColor' => '000000'])->addText(
            'Name of Organization Adviser',
            ['bold' => true, 'size' => 11, 'name' => 'Times New Roman', 'color' => 'FFFFFF'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        // Data rows
        foreach ($organizations as $index => $org) {
            $table->addRow();
            $table->addCell(500, ['valign' => 'center'])->addText(
                ($index + 1) . '.',
                ['size' => 11, 'name' => 'Times New Roman'],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
            $table->addCell(3700, ['valign' => 'center'])->addText(
                strtoupper($org['name']),
                ['size' => 11, 'name' => 'Times New Roman']
            );
            $table->addCell(2500, ['valign' => 'center'])->addText(
                strtoupper($org['president']),
                ['size' => 11, 'name' => 'Times New Roman']
            );
            $table->addCell(3300, ['valign' => 'center'])->addText(
                strtoupper($org['adviser']),
                ['size' => 11, 'name' => 'Times New Roman']
            );
        }
    }

    /**
     * Add signature section to DOCX.
     */
    private function addDocxSignatures($section, $request)
    {
        $section->addTextBreak(2);

        // Prepared By and Noted By (side by side)
        $table = $section->addTable([
            'width' => 100 * 50,
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
        ]);

        $table->addRow();
        
        // Prepared By
        $cellPrepared = $table->addCell(5000, ['borderSize' => 0]);
        $cellPrepared->addText(
            'Prepared by:',
            ['size' => 11, 'name' => 'Calibri']
        );
        $cellPrepared->addTextBreak(2);
        $cellPrepared->addText(
            strtoupper($request->input('prepared_by', 'DANIEL A. GEALONE')),
            ['bold' => true, 'size' => 11, 'name' => 'Calibri']
        );
        $cellPrepared->addText(
            $request->input('prepared_by_title', 'Secretary, OSAS'),
            ['italic' => true, 'size' => 10, 'name' => 'Calibri']
        );

        // Noted By
        $cellNoted = $table->addCell(5000, ['borderSize' => 0]);
        $cellNoted->addText(
            'Noted by:',
            ['size' => 11, 'name' => 'Calibri']
        );
        $cellNoted->addTextBreak(2);
        $cellNoted->addText(
            strtoupper($request->input('noted_by', 'ALJON A. VILLAREAL')),
            ['bold' => true, 'size' => 11, 'name' => 'Calibri']
        );
        $cellNoted->addText(
            $request->input('noted_by_title', 'Coordinator, Student Organization Unit'),
            ['italic' => true, 'size' => 10, 'name' => 'Calibri']
        );

        $section->addTextBreak(2);

        // Approved By
        $section->addText(
            'Approved by:',
            ['size' => 11, 'name' => 'Calibri']
        );
        $section->addTextBreak(2);
        $section->addText(
            strtoupper($request->input('approved_by', 'ALBERTO B. CASTILLO, EdD')),
            ['bold' => true, 'size' => 11, 'name' => 'Calibri']
        );
        $section->addText(
            $request->input('approved_by_title', 'Director, OSAS'),
            ['italic' => true, 'size' => 10, 'name' => 'Calibri']
        );
    }
}