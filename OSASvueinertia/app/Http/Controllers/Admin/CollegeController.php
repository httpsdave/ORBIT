<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CollegeController extends Controller
{
    /**
     * Display a listing of colleges.
     */
    public function index()
    {
        $colleges = College::withCount('studentOrgs')->get();
        
        return Inertia::render('Admin/Colleges/Index', [
            'colleges' => $colleges
        ]);
    }

    /**
     * Store a newly created college.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'required|string|max:50|unique:colleges',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'acronym' => $validated['acronym'],
            'description' => $validated['description'],
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('college-logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        College::create($data);

        return redirect()->route('admin.colleges.index')
            ->with('message', 'College created successfully.');
    }

    /**
     * Update the specified college.
     */
    public function update(Request $request, College $college)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'required|string|max:50|unique:colleges,acronym,' . $college->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'acronym' => $validated['acronym'],
            'description' => $validated['description'],
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($college->logo_path) {
                \Storage::disk('public')->delete($college->logo_path);
            }
            
            $logoPath = $request->file('logo')->store('college-logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        $college->update($data);

        return redirect()->route('admin.colleges.index')
            ->with('message', 'College updated successfully.');
    }

    /**
     * Remove the specified college.
     */
    public function destroy(College $college)
    {
        // Check if college has associated student organizations
        if ($college->studentOrgs()->count() > 0) {
            return redirect()->route('admin.colleges.index')
                ->with('error', 'Cannot delete college with associated student organizations.');
        }

        $college->delete();

        return redirect()->route('admin.colleges.index')
            ->with('message', 'College deleted successfully.');
    }

    /**
     * Get all colleges (for API).
     */
    public function getAll()
    {
        $colleges = College::all();
        
        return response()->json($colleges);
    }
}