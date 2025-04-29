<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FormController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OrganizationApplicationController;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use App\Models\Role;

// Authentication routes (login, register, password reset)
require __DIR__.'/auth.php';

// ALL routes protected behind authentication
Route::middleware(['auth'])->group(function () {
    // Frontend routes
    Route::get('/', 'App\Http\Controllers\FrontendController@index')->name('myhome');
    Route::get('/about', 'App\Http\Controllers\FrontendController@about')->name('aboutUs');
    Route::inertia('/contact', 'Frontend/Contact')->name('contactUs');
    
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
    
    Route::get('/applications/{application}/pdf', [OrganizationApplicationController::class, 'exportPdf'])->name('applications.pdf');
    Route::get('/applications/{application}/export-renewal', [OrganizationApplicationController::class, 'exportRenewalPdf'])->name('applications.export-renewal');
    Route::get('/applications/{application}/export-commitment', [OrganizationApplicationController::class, 'exportCommitmentPdf'])->name('applications.export-commitment');
    Route::get('/applications/{application}/export-plan', [OrganizationApplicationController::class, 'exportPlanPdf'])->name('applications.export-plan');
    Route::get('/applications/{application}/export-certification', [OrganizationApplicationController::class, 'exportCertificationPdf'])->name('applications.export-certification');
    Route::get('/applications/{application}/export-members', [OrganizationApplicationController::class, 'exportMembersPdf'])->name('applications.export-members');
    Route::get('/applications/{application}/export-officers', [OrganizationApplicationController::class, 'exportOfficersPdf'])->name('applications.export-officers');
    Route::get('/applications/{application}/export-attendance', [OrganizationApplicationController::class, 'exportAttendancePdf'])->name('applications.export-attendance');

    // User dashboard route
    Route::get('/dashboard', function () {
        // Redirect admins to admin dashboard
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        return Inertia::render('Dashboard');
    })->middleware(['verified'])->name('dashboard');
    
    // Admin routes
    

    Route::middleware(['verified', CheckRole::class.':admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
        
        // Other admin routes
        Route::get('/users', function () {
            return Inertia::render('Admin/Users', [
                'users' => User::with('role')->get()
            ]);
        })->name('admin.users');
    });

    Route::middleware(['verified', CheckRole::class.':admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
        
        // Admin User Management Routes
        Route::get('/users', 'App\Http\Controllers\Admin\UserController@index')->name('admin.users');
        Route::post('/users', 'App\Http\Controllers\Admin\UserController@store')->name('admin.users.store');
        Route::put('/users/{user}', 'App\Http\Controllers\Admin\UserController@update')->name('admin.users.update');
        Route::delete('/users/{user}', 'App\Http\Controllers\Admin\UserController@destroy')->name('admin.users.destroy');
    });
});

// Redirect root to login if not authenticated
Route::redirect('/', '/login');