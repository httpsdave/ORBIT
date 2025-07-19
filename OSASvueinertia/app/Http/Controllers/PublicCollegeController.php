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
            'colleges' => $colleges
        ]);
    }

    /**
     * Display the specified college.
     */
    public function show(College $college)
    {
        $college->load('users');
        
        return Inertia::render('Colleges/Show', [
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