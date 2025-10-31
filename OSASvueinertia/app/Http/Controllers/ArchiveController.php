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

        $perPage = $request->get('per_page', 20);
        $paginatedApplications = $query->orderBy('archived_at', 'desc')->paginate($perPage);

        // Get unique academic years for filter
        $academicYears = OrganizationApplication::archived()
            ->where('user_id', auth()->id())
            ->whereNotNull('academic_year_archived')
            ->distinct()
            ->pluck('academic_year_archived')
            ->sort()
            ->values();

        return Inertia::render('Archive/Index', [
            'archivedApplications' => $paginatedApplications->items(),
            'academicYears' => $academicYears,
            'currentAcademicYearFilter' => $request->academic_year_filter,
            'currentPage' => $paginatedApplications->currentPage(),
            'hasMorePages' => $paginatedApplications->hasMorePages(),
            'perPage' => $perPage,
            'successMessage' => session('success'),
            'errorMessage' => session('error'),
        ]);
    }

    /**
     * Load more archived applications for infinite scroll
     */
    public function loadMore(Request $request)
    {
        $query = OrganizationApplication::archived()
            ->where('user_id', auth()->id())
            ->with(['user', 'archivedBy']);
        
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        // Apply academic year filter if provided
        if ($request->filled('academic_year_filter')) {
            $query->where('academic_year_archived', $request->academic_year_filter);
        }

        $paginatedApplications = $query->orderBy('archived_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'archivedApplications' => $paginatedApplications->items(),
            'currentPage' => $paginatedApplications->currentPage(),
            'hasMorePages' => $paginatedApplications->hasMorePages(),
            'perPage' => $perPage,
        ]);
    }
} 