<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FormController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OrganizationApplicationController;

Route::get('/','App\Http\Controllers\FrontendController@index')->name('myhome');
Route::get('/about','App\Http\Controllers\FrontendController@about')->name('aboutUs');
Route::inertia('/contact','Frontend/Contact')->name('contactUs');



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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
