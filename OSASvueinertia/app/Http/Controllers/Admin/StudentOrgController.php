<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\StudentOrg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentOrgController extends Controller
{
    /**
     * Display the student organizations management page.
     */
    public function index()
    {
        $colleges = College::with('studentOrgs')->get();
        
        return Inertia::render('Admin/StudentOrgs/Index', [
            'colleges' => $colleges
        ]);
    }

    /**
     * Store a newly created student organization.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }

        $studentOrg = StudentOrg::create($validated);

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'Student organization created successfully.');
    }

    /**
     * Update the specified student organization.
     */
    public function update(Request $request, StudentOrg $studentOrg)
    {
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($studentOrg->logo_path) {
                Storage::disk('public')->delete($studentOrg->logo_path);
            }
            
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }

        $studentOrg->update($validated);

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'Student organization updated successfully.');
    }

    /**
     * Remove the specified student organization.
     */
    public function destroy(StudentOrg $studentOrg)
    {
        // Delete logo if exists
        if ($studentOrg->logo_path) {
            Storage::disk('public')->delete($studentOrg->logo_path);
        }

        $studentOrg->delete();

        return redirect()->route('admin.student-orgs.index')
            ->with('message', 'Student organization deleted successfully.');
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