<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicCollegeController extends Controller
{
    /**
     * Display a listing of colleges.
     */
    public function index()
    {
        $colleges = College::withCount('users')->get();
        
        return Inertia::render('Colleges/Index', [
            'auth' => [
                'user' => auth()->user()
            ],
            'colleges' => $colleges
        ]);
    }

    /**
     * Display the specified college.
     */
    public function show(College $college)
    {
        // Load users without the college relationship to prevent circular references
        $college->load(['users' => function ($query) {
            // Only select necessary fields and don't load the college relationship
            $query->select('id', 'name', 'email', 'profile_photo_path', 'college_id', 'description', 'status');
        }]);
        
        return Inertia::render('Colleges/Show', [
            'auth' => [
                'user' => auth()->user()
            ],
            'college' => $college
        ]);
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