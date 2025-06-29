<?php

namespace App\Http\Controllers;

use App\Models\OrganizationApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    /**
     * Display the user's archived applications
     */
    public function index(Request $request)
    {
        $query = OrganizationApplication::archived()
            ->where('user_id', auth()->id())
            ->with(['user', 'archivedBy']);

        // Apply academic year filter if provided
        if ($request->filled('academic_year_filter')) {
            $query->where('academic_year_archived', $request->academic_year_filter);
        }

        $archivedApplications = $query->orderBy('archived_at', 'desc')->get();

        // Get unique academic years for filter
        $academicYears = OrganizationApplication::archived()
            ->where('user_id', auth()->id())
            ->whereNotNull('academic_year_archived')
            ->distinct()
            ->pluck('academic_year_archived')
            ->sort()
            ->values();

        return Inertia::render('Archive/Index', [
            'archivedApplications' => $archivedApplications,
            'academicYears' => $academicYears,
            'currentAcademicYearFilter' => $request->academic_year_filter,
            'successMessage' => session('success'),
            'errorMessage' => session('error'),
        ]);
    }
} 