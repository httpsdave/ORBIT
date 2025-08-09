<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\StudentOrg;
use App\Models\User;
use App\Models\OrganizationApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicStudentOrgController extends Controller
{
    /**
     * Display the student organizations page.
     */
    public function index()
    {
        // Get the admin role id
        $adminRoleId = \App\Models\Role::where('slug', 'admin')->value('id');

        // Get all users who are not admins and have a college_id set
        $organizations = \App\Models\User::with('college')
            ->where('role_id', '!=', $adminRoleId)
            ->whereNotNull('college_id')
            ->get();

        return Inertia::render('StudentOrgs/Index', [
            'organizations' => $organizations
        ]);
    }

    /**
     * Display the specified student organization.
     */
    public function show(User $studentOrg)
    {
        $studentOrg->load('college');
        
        // Get organization details from latest approved applications
        // Latest approved List of Members form
        $latestMembersApp = OrganizationApplication::with('members')
            ->where('user_id', $studentOrg->id)
            ->where('status', 'Approved')
            ->where('form_type', 'LSPU-OSAS-SF-005')
            ->orderByDesc('created_at')
            ->first();
            
        // Latest approved List of Officers form
        $latestOfficersApp = OrganizationApplication::with('officers')
            ->where('user_id', $studentOrg->id)
            ->where('status', 'Approved')
            ->where('form_type', 'LSPU-OSAS-SF-007')
            ->orderByDesc('created_at')
            ->first();
        
        // Get adviser information from the latest available form (prefer members, fallback to officers)
        $adviser_name = $latestMembersApp->adviser_name ?? $latestOfficersApp->adviser_name ?? null;
        $second_adviser = $latestMembersApp->second_adviser ?? $latestOfficersApp->second_adviser ?? null;
        
        // Get members and officers from the latest approved forms
        $members = $latestMembersApp ? $latestMembersApp->members : collect();
        $officers = $latestOfficersApp ? $latestOfficersApp->officers : collect();
        
        // Prepare organization details
        $organizationDetails = [
            'adviser_name' => $adviser_name,
            'second_adviser' => $second_adviser,
            'members_count' => $members->count(),
            'officers_count' => $officers->count(),
            'members' => $members,
            'officers' => $officers,
            'has_approved_data' => $latestMembersApp || $latestOfficersApp
        ];
        
        return Inertia::render('StudentOrgs/Show', [
            'studentOrg' => $studentOrg,
            'organizationDetails' => $organizationDetails
        ]);
    }

    /**
     * Get all student organizations grouped by college (for API).
     */
    public function getAll()
    {
        $colleges = College::with('users')->get();
        
        return response()->json($colleges);
    }
}