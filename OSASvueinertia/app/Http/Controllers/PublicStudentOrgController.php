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
        $colleges = College::with('studentOrgs')->get();
        
        return Inertia::render('StudentOrgs/Index', [
            'colleges' => $colleges
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