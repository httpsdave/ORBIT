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
        $colleges = College::with(['users.role', 'users.parentOrganization', 'users.subOrganizations'])->get();
        $users = User::with(['role', 'parentOrganization', 'subOrganizations'])->get(); // For selection modal
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
}