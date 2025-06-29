<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveController extends Controller
{
    /**
     * Display the archive index page
     */
    public function index(Request $request)
    {
        $query = OrganizationApplication::archived()->with(['user', 'archivedBy']);
        
        // Apply user filter if provided
        if ($request->filled('user_filter')) {
            $query->where('user_id', $request->user_filter);
        }

        // Apply academic year filter if provided
        if ($request->filled('academic_year_filter')) {
            $query->where('academic_year_archived', $request->academic_year_filter);
        }

        $archivedApplications = $query->orderBy('archived_at', 'desc')->get();

        // Get all users who have archived applications for the filter dropdown
        $users = \App\Models\User::whereHas('organizationApplications', function($query) {
            $query->where('is_archived', true);
        })->select('id', 'name', 'student_org_id')
        ->orderBy('name')
        ->get();

        // Get unique academic years for filter
        $academicYears = OrganizationApplication::archived()
            ->whereNotNull('academic_year_archived')
            ->distinct()
            ->pluck('academic_year_archived')
            ->sort()
            ->values();

        return Inertia::render('Admin/Archive/Index', [
            'archivedApplications' => $archivedApplications,
            'users' => $users,
            'academicYears' => $academicYears,
            'currentUserFilter' => $request->user_filter,
            'currentAcademicYearFilter' => $request->academic_year_filter,
            'successMessage' => session('success'),
            'errorMessage' => session('error'),
        ]);
    }

    /**
     * End the year - archive all current applications
     */
    public function endYear(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string|max:20',
            'confirmation' => 'required|string|in:END_YEAR'
        ]);

        try {
            DB::beginTransaction();

            // Get all non-archived applications
            $applications = OrganizationApplication::active()->get();
            
            if ($applications->isEmpty()) {
                return back()->with('error', 'No applications to archive.');
            }

            $archivedCount = 0;
            foreach ($applications as $application) {
                $application->update([
                    'is_archived' => true,
                    'archived_at' => now(),
                    'archived_by' => auth()->id(),
                    'academic_year_archived' => $request->academic_year
                ]);
                $archivedCount++;
            }

            DB::commit();

            return redirect()->route('applications.index')->with('success', "Successfully archived {$archivedCount} applications for academic year {$request->academic_year}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('applications.index')->with('error', 'Failed to archive applications. Please try again.');
        }
    }

    /**
     * Restore an archived application (admin only)
     */
    public function restore(OrganizationApplication $application)
    {
        if (!$application->is_archived) {
            return back()->with('error', 'This application is not archived.');
        }

        $application->update([
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null,
            'academic_year_archived' => null
        ]);

        return back()->with('success', 'Application restored successfully.');
    }

    /**
     * Get archive statistics for dashboard
     */
    public function getArchiveStats()
    {
        $totalArchived = OrganizationApplication::archived()->count();
        $recentlyArchived = OrganizationApplication::archived()
            ->where('archived_at', '>=', now()->subDays(30))
            ->count();
        
        $academicYears = OrganizationApplication::archived()
            ->whereNotNull('academic_year_archived')
            ->distinct()
            ->pluck('academic_year_archived')
            ->sort()
            ->values();

        return response()->json([
            'totalArchived' => $totalArchived,
            'recentlyArchived' => $recentlyArchived,
            'academicYears' => $academicYears
        ]);
    }
} 