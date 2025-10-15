<?php

namespace App\Http\Controllers;

use App\Models\AutosavedForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormAutosaveController extends Controller
{
    /**
     * Save or update autosaved form data.
     */
    public function save(Request $request)
    {
        $request->validate([
            'form_type' => 'required|string|max:255',
            'form_data' => 'required|array',
        ]);

        $autosaved = AutosavedForm::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'form_type' => $request->form_type,
            ],
            [
                'form_data' => $request->form_data,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Form autosaved successfully',
            'updated_at' => $autosaved->updated_at,
        ]);
    }

    /**
     * Get autosaved form data for the authenticated user.
     */
    public function get(Request $request)
    {
        $request->validate([
            'form_type' => 'required|string|max:255',
        ]);

        $autosaved = AutosavedForm::where('user_id', Auth::id())
            ->where('form_type', $request->form_type)
            ->first();

        if (!$autosaved) {
            return response()->json([
                'success' => false,
                'message' => 'No autosaved data found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'form_data' => $autosaved->form_data,
            'updated_at' => $autosaved->updated_at,
        ]);
    }

    /**
     * Delete autosaved form data after successful submission.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'form_type' => 'required|string|max:255',
        ]);

        AutosavedForm::where('user_id', Auth::id())
            ->where('form_type', $request->form_type)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Autosaved data cleared',
        ]);
    }
}
