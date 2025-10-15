<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembersOfficersController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        // Build base query for List of Members applications (LSPU-OSAS-SF-005)
        $membersQuery = OrganizationApplication::where('form_type', 'LSPU-OSAS-SF-005')
            ->with(['user', 'members']);

        // Build base query for List of Officers applications (LSPU-OSAS-SF-007)
        $officersQuery = OrganizationApplication::where('form_type', 'LSPU-OSAS-SF-007')
            ->with(['user', 'officers']);

        // If not admin, only show the user's own organization data
        if (!$isAdmin) {
            $membersQuery->where('user_id', $user->id);
            $officersQuery->where('user_id', $user->id);
        }

        $membersApplications = $membersQuery->get();
        $officersApplications = $officersQuery->get();

        // Flatten members from all applications with organization info
        $members = [];
        foreach ($membersApplications as $application) {
            foreach ($application->members as $member) {
                $members[] = [
                    'id' => $member->id,
                    'application_id' => $application->id,
                    'organization' => $application->user->name,
                    'student_name' => $member->student_name,
                    'student_number' => $member->student_number,
                    'course_year_section' => $member->course_year_section ?? 'N/A',
                    'semester' => $application->semester ?? 'N/A',
                    'academic_year' => $application->academic_year_start . '-' . $application->academic_year_end,
                    'status' => $application->status,
                    'submitted_at' => $application->created_at->format('M d, Y'),
                ];
            }
        }

        // Flatten officers from all applications with organization info
        $officers = [];
        foreach ($officersApplications as $application) {
            foreach ($application->officers as $officer) {
                $officers[] = [
                    'id' => $officer->id,
                    'application_id' => $application->id,
                    'organization' => $application->user->name,
                    'student_name' => $officer->student_name,
                    'position' => $officer->position,
                    'student_number' => $officer->student_number,
                    'status' => $application->status,
                    'submitted_at' => $application->created_at->format('M d, Y'),
                ];
            }
        }

        return Inertia::render('Admin/MembersOfficers/Index', [
            'members' => $members,
            'officers' => $officers,
            'totalMembers' => count($members),
            'totalOfficers' => count($officers),
            'isAdmin' => $isAdmin,
        ]);
    }
}
