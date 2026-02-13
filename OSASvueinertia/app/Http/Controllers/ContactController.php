<?php

namespace App\Http\Controllers;

use App\Mail\ContactFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        return Inertia::render('Contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string|in:Bug Report,Suggestion,Difficulty,General Inquiry',
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();

        try {
            Mail::to('lspuorbit@gmail.com')->send(
                new ContactFeedback(
                    senderName: $user->name ?? $user->email,
                    senderEmail: $user->email,
                    feedbackSubject: $validated['subject'],
                    feedbackMessage: $validated['message'],
                    category: $validated['category'],
                )
            );

            return back()->with('success', '✅ Message sent successfully! We\'ll review your feedback and get back to you as soon as possible.');
        } catch (\Exception $e) {
            \Log::error('Contact form email failed: ' . $e->getMessage());

            return back()->with('error', '❌ Unable to send your message at this time. Please try again or contact us directly at lspuorbit@gmail.com.');
        }
    }
}
