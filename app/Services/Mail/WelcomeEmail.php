<?php

namespace App\Services\Mail;

use App\Models\EmailQueue;
use App\Models\User;

/** The welcome email a person gets once, right after their email address is verified. */
class WelcomeEmail
{
    /** Store the email in the queue (the worker sends it). Returns false when this person already got one. */
    public function queue(User $user): bool
    {
        if (EmailQueue::where('type', 'welcome')->where('to', $user->email)->exists()) {
            return false;
        }
        app(EmailQueueService::class)->enqueue(
            $user->email,
            'Welcome to Flikma — your account is ready',
            'emails.auth.welcome',
            [
                'userName' => $user->name,
                'companyName' => $user->company_id ? \App\Models\Master\Company::withoutGlobalScopes()->find($user->company_id)?->name : null,
                'dashboardUrl' => url('/dashboard'),
                'supportEmail' => 'support@flikma.com',
            ],
            ['type' => 'welcome', 'company_id' => $user->company_id]
        );

        return true;
    }
}
