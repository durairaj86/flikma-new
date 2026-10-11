<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;

/** The email that carries the one-time verification code. */
class VerificationOtpMail extends Mailable
{
    public function __construct(public User $user, public string $code, public int $minutes = 5)
    {
    }

    public function build(): self
    {
        // The sender name is the product name, whatever APP_NAME is set to.
        return $this->from(config('mail.from.address'), 'Flikma')
            ->subject('Your Flikma verification code')
            ->view('emails.auth.verification-otp')
            ->text('emails.auth.verification-otp-text');
    }
}
