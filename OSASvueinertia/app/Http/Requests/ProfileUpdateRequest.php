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
        }

        return $rules;
    }
}
