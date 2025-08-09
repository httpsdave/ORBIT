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
        $colleges = College::with('users.role')->get();
        $users = User::with('role')->get(); // For selection modal
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
}