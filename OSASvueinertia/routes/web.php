<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OrganizationApplicationController;
use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\StudentOrgController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PublicCollegeController;
use App\Http\Controllers\PublicStudentOrgController;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use App\Models\Role;

// Authentication routes (login, register, password reset)
require __DIR__.'/auth.php';

// Redirect root to login if not authenticated
Route::redirect('/', '/login');

// ALL routes protected behind authentication
Route::middleware(['auth'])->group(function () {

    // Change default redirect to dashboard instead of calendar
    Route::get('/', function () {
        return redirect('/dashboard');
    });
    
    // Ensure user role is passed to calendar view
    Route::get('/calendar', [EventController::class, 'index'])->name('calendar');

    // API routes
    Route::prefix('api')->group(function () {
        // Ensure these routes check for admin role
        Route::middleware(['auth'])->group(function () {
            Route::post('/events', [EventController::class, 'store']);
            Route::put('/events/{event}', [EventController::class, 'update']);
            Route::delete('/events/{event}', [EventController::class, 'destroy']);
            Route::post('/extract-event-info', [EventController::class, 'extractEventInfo']);
        });
        
        // This route can be accessed by all authenticated users
        Route::get('/events', [EventController::class, 'getEvents']);
        
        // API routes for colleges and student orgs
        Route::get('/colleges', [PublicCollegeController::class, 'getAll'])->name('api.colleges.all');
        Route::get('/student-orgs', [PublicStudentOrgController::class, 'getAll'])->name('api.student-orgs.all');
    });
    
    
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Applications routes
    Route::get('/applications', [OrganizationApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [OrganizationApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [OrganizationApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}/edit', [OrganizationApplicationController::class, 'edit'])->name('applications.edit');
    Route::put('/applications/{application}', [OrganizationApplicationController::class, 'update'])->name('applications.update');
    Route::delete('/applications/{application}', [OrganizationApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::post('/applications/{application}/update-status', [OrganizationApplicationController::class, 'updateStatus'])->name('applications.update-status');

    // PDF export routes
    Route::get('/applications/{application}/pdf', [OrganizationApplicationController::class, 'exportPdf'])->name('applications.pdf');
    Route::get('/applications/{application}/export-renewal', [OrganizationApplicationController::class, 'exportRenewalPdf'])->name('applications.export-renewal');
    Route::get('/applications/{application}/export-commitment', [OrganizationApplicationController::class, 'exportCommitmentPdf'])->name('applications.export-commitment');
    Route::get('/applications/{application}/export-plan', [OrganizationApplicationController::class, 'exportPlanPdf'])->name('applications.export-plan');
    Route::get('/applications/{application}/export-certification', [OrganizationApplicationController::class, 'exportCertificationPdf'])->name('applications.export-certification');
    Route::get('/applications/{application}/export-members', [OrganizationApplicationController::class, 'exportMembersPdf'])->name('applications.export-members');
    Route::get('/applications/{application}/export-officers', [OrganizationApplicationController::class, 'exportOfficersPdf'])->name('applications.export-officers');
    Route::get('/applications/{application}/export-attendance', [OrganizationApplicationController::class, 'exportAttendancePdf'])->name('applications.export-attendance');

    // Public Routes for Colleges and Student Organizations (view-only)
    Route::get('/colleges', [PublicCollegeController::class, 'index'])->name('colleges.index');
    Route::get('/colleges/{college}', [PublicCollegeController::class, 'show'])->name('colleges.show');
    
    Route::get('/student-orgs', [PublicStudentOrgController::class, 'index'])->name('student-orgs.index');
    Route::get('/student-orgs/{studentOrg}', [PublicStudentOrgController::class, 'show'])->name('student-orgs.show');


    // Upload signed document
    Route::post('/applications/{application}/upload-document', [OrganizationApplicationController::class, 'uploadSignedDocument'])
    ->name('applications.upload-document');

    // View signed document
    Route::get('/applications/{application}/view-document', [OrganizationApplicationController::class, 'viewSignedDocument'])
    ->name('applications.view-document');

    // Delete signed document
    Route::delete('/applications/{application}/delete-document', [OrganizationApplicationController::class, 'deleteSignedDocument'])
    ->name('applications.delete-document');

    // User dashboard route with admin redirect
    Route::get('/dashboard', function () {
        // Redirect admins to admin dashboard
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Forward to the UserDashboardController for regular users
        return app(UserDashboardController::class)->index(request());
    })->middleware(['verified'])->name('dashboard');
    

    // Admin routes - SINGLE consolidated admin route group
    Route::middleware(['verified', CheckRole::class.':admin'])->prefix('admin')->group(function () {
        // Use DashboardController for dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        // Admin User Management Routes
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Student Organizations Management Routes
        Route::get('/student-orgs', [StudentOrgController::class, 'index'])->name('admin.student-orgs.index');
        Route::post('/student-orgs', [StudentOrgController::class, 'store'])->name('admin.student-orgs.store');
        Route::put('/student-orgs/{studentOrg}', [StudentOrgController::class, 'update'])->name('admin.student-orgs.update');
        Route::delete('/student-orgs/{studentOrg}', [StudentOrgController::class, 'destroy'])->name('admin.student-orgs.destroy');
        Route::get('/student-orgs/all', [StudentOrgController::class, 'getAll'])->name('admin.student-orgs.all');
         
        // College Management Routes
        Route::get('/colleges', [CollegeController::class, 'index'])->name('admin.colleges.index');
        Route::post('/colleges', [CollegeController::class, 'store'])->name('admin.colleges.store');
        Route::put('/colleges/{college}', [CollegeController::class, 'update'])->name('admin.colleges.update');
        Route::delete('/colleges/{college}', [CollegeController::class, 'destroy'])->name('admin.colleges.destroy');
        Route::get('/colleges/all', [CollegeController::class, 'getAll'])->name('admin.colleges.all');
    });
});