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
}