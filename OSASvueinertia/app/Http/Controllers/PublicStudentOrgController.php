<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\StudentOrg;
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
    public function show(StudentOrg $studentOrg)
    {
        $studentOrg->load('college');
        
        return Inertia::render('StudentOrgs/Show', [
            'studentOrg' => $studentOrg
        ]);
    }

    /**
     * Get all student organizations grouped by college (for API).
     */
    public function getAll()
    {
        $colleges = College::with('studentOrgs')->get();
        
        return response()->json($colleges);
    }
}