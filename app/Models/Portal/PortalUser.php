<?php

namespace App\Models\Portal;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

/**
 * A customer's login to the customer portal (guard "portal"). Belongs to one customer of one company; everything
 * the portal shows is filtered by both. Staff invite them from the customer page; the invite / reset link carries a
 * random token of which only a SHA-256 hash is stored.
 */
class PortalUser extends Authenticatable
{
    protected $table = 'customer_portal_users';

    protected $hidden = ['password', 'remember_token', 'invite_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'invite_expires_at' => 'datetime',
        'password_set_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** Hours a set-password invite stays valid; a password reset link is valid for one hour. */
    public const INVITE_HOURS = 72;
    public const RESET_HOURS = 1;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withoutGlobalScopes();
    }

    /** New link token (returned once, to put in the email); only its hash is kept. */
    public function issueToken(int $hours): string
    {
        $token = Str::random(48);
        $this->invite_token = hash('sha256', $token);
        $this->invite_expires_at = now()->addHours($hours);
        $this->save();

        return $token;
    }

    public static function findByToken(string $token): ?self
    {
        $user = static::where('invite_token', hash('sha256', $token))->first();

        return $user && $user->invite_expires_at && $user->invite_expires_at->isFuture() ? $user : null;
    }

    /** Shown on the staff side. */
    public function statusLabel(): string
    {
        if (!$this->is_active) {
            return __('Disabled');
        }

        return $this->password_set_at ? __('Active') : __('Invited');
    }
}
