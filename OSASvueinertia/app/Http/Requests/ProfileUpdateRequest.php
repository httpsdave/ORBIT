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

        $rules = [
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'], // 2MB
        ];

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
