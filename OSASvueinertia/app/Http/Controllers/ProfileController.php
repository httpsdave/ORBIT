<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $isAdmin = $user && ($user->role === 'admin' || (is_object($user->role) && (isset($user->role->name) && $user->role->name === 'admin' || isset($user->role->slug) && $user->role->slug === 'admin' || isset($user->role->id) && $user->role->id === 1)));

        // Enforce 14-day restriction for non-admins
        if (!$isAdmin && isset($data['name']) && $data['name'] !== $user->name) {
            $lastChange = $user->last_name_change_at;
            if ($lastChange && now()->diffInDays($lastChange) < 14) {
                return Redirect::back()->withErrors(['name' => 'You can only change your name once every 14 days.']);
            }
            // Update the last_name_change_at timestamp
            $data['last_name_change_at'] = now();
        } else if ($isAdmin && isset($data['name']) && $data['name'] !== $user->name) {
            // Admins can change any time, update timestamp for audit
            $data['last_name_change_at'] = now();
        }

        // Handle special value for removing profile photo
        if ($request->input('profile_photo') === '__REMOVE__') {
            if ($user->profile_photo_path && \Storage::disk('public')->exists($user->profile_photo_path)) {
                \Storage::disk('public')->delete($user->profile_photo_path);
            }
            $data['profile_photo_path'] = null;
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
