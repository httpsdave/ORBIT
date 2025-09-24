<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('social_links') && is_array($this->input('social_links'))) {
            $clean = [];
            foreach ($this->input('social_links') as $link) {
                if (!is_array($link)) continue;
                $platform = isset($link['platform']) ? trim($link['platform']) : '';
                $url = isset($link['url']) ? trim($link['url']) : '';

                if ($platform === '' || $url === '') {
                    continue; // drop empty entries
                }

                // If URL missing scheme, prefix https://
                if (!preg_match('#^https?://#i', $url)) {
                    $url = 'https://' . ltrim($url, '/');
                }

                $clean[] = [
                    'platform' => $platform,
                    'url' => $url,
                ];
            }

            $this->merge(['social_links' => $clean]);
        }
    }
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

        // Social links validation - optional but if provided, validate structure
        $rules['social_links'] = ['nullable', 'array'];
        $rules['social_links.*.platform'] = ['nullable', 'string', 'max:50'];
        $rules['social_links.*.url'] = ['nullable', 'url', 'max:255'];

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
