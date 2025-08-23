<?php

namespace App\Services;

use App\Models\UserFormData;
use Illuminate\Support\Facades\Auth;

class FormDataService
{
    /**
     * Save form data for the authenticated user
     */
    public static function saveFormData(array $formData): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return;
        }

        foreach ($formData as $fieldName => $fieldValue) {
            // Skip empty values, form_type, and array fields
            if (empty($fieldValue) || 
                $fieldName === 'form_type' || 
                is_array($fieldValue) ||
                is_object($fieldValue)) {
                continue;
            }

            UserFormData::updateOrCreate(
                [
                    'user_id' => $userId,
                    'field_name' => $fieldName,
                ],
                [
                    'field_value' => $fieldValue,
                ]
            );
        }
    }

    /**
     * Get saved form data for the authenticated user
     */
    public static function getSavedFormData(): array
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return [];
        }

        return UserFormData::where('user_id', $userId)
            ->pluck('field_value', 'field_name')
            ->toArray();
    }

    /**
     * Get saved form data for specific fields
     */
    public static function getSavedFormDataForFields(array $fields): array
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return [];
        }

        return UserFormData::where('user_id', $userId)
            ->whereIn('field_name', $fields)
            ->pluck('field_value', 'field_name')
            ->toArray();
    }

    /**
     * Clear all saved form data for the authenticated user
     */
    public static function clearSavedFormData(): bool
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return false;
        }

        return UserFormData::where('user_id', $userId)->delete() > 0;
    }
} 