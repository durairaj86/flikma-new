<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('website.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'interest' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        // A misconfigured/unavailable mail provider must not block the
        // visitor's submission — the message is already saved above and
        // visible to staff regardless of whether the notification email
        // goes out.
        try {
            Mail::to('support@flikma.com')->send(new ContactMessageReceived($contactMessage));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact form notification email: '.$e->getMessage());
        }

        return redirect()
            ->route('website.contact')
            ->with('contact_success', 'Thanks! Our team will reach out within one business day to schedule your demo.');
    }
}
