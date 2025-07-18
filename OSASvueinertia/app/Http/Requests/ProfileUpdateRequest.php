<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isAdmin = $user && ($user->role === 'admin' || (is_object($user->role) && (isset($user->role->name) && $user->role->name === 'admin' || isset($user->role->slug) && $user->role->slug === 'admin' || isset($user->role->id) && $user->role->id === 1)));

        $rules = [];

        // Dynamically build the profile_photo rule
        if ($this->input('profile_photo') !== '__REMOVE__') {
            $rules['profile_photo'] = ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'];
        } else {
            $rules['profile_photo'] = ['nullable'];
        }

        // Description field validation
        $rules['description'] = ['nullable', 'string', 'max:1000'];

        if ($isAdmin) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ];
        } else {
            // For non-admins, enforce name validation and 14-day restriction
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== $user->name && $user->last_name_change_at) {
                        $now = now();
                        $nextAllowed = $user->last_name_change_at->copy()->addDays(14);
                        if ($now->lt($nextAllowed)) {
                            $diff = $now->diff($nextAllowed);
                            $days = $diff->d;
                            $hours = $diff->h;
                            $msg = 'You can change your name in ';
                            if ($days > 0) {
                                $msg .= $days . ' day' . ($days > 1 ? 's' : '');
                            }
                            if ($hours > 0) {
                                if ($days > 0) $msg .= ' and ';
                                $msg .= $hours . ' hour' . ($hours > 1 ? 's' : '');
                            }
                            $msg .= '.';
                            $fail($msg);
                        }
                    }
                },
            ];
        }

        return $rules;
    }
}
