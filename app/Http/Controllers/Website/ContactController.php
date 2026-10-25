<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // Stored in the email queue; the worker sends it (and retries if the mail server is down).
        try {
            app(\App\Services\Mail\EmailQueueService::class)->enqueue(
                \App\Models\Admin\Setting::get('admin_email') ?: 'support@flikma.com', 'New Contact Form Submission - ' . $contactMessage->name, 'emails.website.contact-message',
                ['contactMessage' => $contactMessage->only(['name', 'email', 'company', 'phone', 'interest', 'message']) + ['submitted' => $contactMessage->created_at?->format('d M Y, h:i A')]],
                ['type' => 'contact', 'from_address' => config('mail.from.address'), 'from_name' => 'Flikma Website']
            );
        } catch (\Throwable $e) {
            Log::error('Failed to queue contact form notification email: '.$e->getMessage());
        }

        return redirect()
            ->route('website.contact')
            ->with('contact_success', 'Thanks! Our team will reach out within one business day to schedule your demo.');
    }
}
