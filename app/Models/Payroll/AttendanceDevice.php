<?php

namespace App\Models\Payroll;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceDevice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'protocol', 'serial_number', 'token_hash', 'token_hint',
        'timezone', 'is_active', 'last_seen_at', 'last_punch_at', 'last_ip',
        'public_id', 'mode', 'field_map', 'pull_url', 'pull_method', 'pull_headers', 'pull_params',
        'pull_interval', 'last_pulled_at', 'last_pull_status', 'last_pull_message',
    ];

    protected $hidden = ['token_hash', 'pull_headers'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_punch_at' => 'datetime',
        'last_pulled_at' => 'datetime',
        'field_map' => 'array',
        'pull_headers' => 'encrypted:array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $d) {
            $d->public_id ??= (string) Str::uuid();
        });
    }

    public function callbackUrl(): string
    {
        return url('/api/attendance/hook/' . $this->public_id);
    }

    public function isPull(): bool
    {
        return $this->protocol === 'api' && $this->mode === 'pull';
    }

    /** Pull devices that are due for their next poll. */
    public function isDue(): bool
    {
        return $this->is_active && $this->isPull() && $this->pull_url
            && (!$this->last_pulled_at || $this->last_pulled_at->lte(now()->subMinutes(max(1, (int) $this->pull_interval))));
    }

    public const PROTOCOLS = [
        'api' => 'HTTP / JSON API',
        'zkteco' => 'ZKTeco / ADMS push',
    ];

    /** The pseudo-device that Excel imports are recorded against. */
    public static function forImport(int $companyId, string $timezone): self
    {
        return static::withoutGlobalScopes()->firstOrCreate(
            ['company_id' => $companyId, 'protocol' => 'import'],
            ['name' => 'Excel import', 'timezone' => $timezone, 'is_active' => true, 'mode' => 'push']
        );
    }

    /** Pseudo-device that manually keyed punches are recorded against. */
    public static function forManual(int $companyId): self
    {
        return static::withoutGlobalScopes()->firstOrCreate(
            ['company_id' => $companyId, 'protocol' => 'manual'],
            ['name' => 'Manual entry', 'timezone' => 'Asia/Riyadh', 'is_active' => true, 'mode' => 'push']
        );
    }

    public function punches(): HasMany
    {
        return $this->hasMany(AttendancePunch::class, 'device_id');
    }

    /** Generates a new secret, stores only its hash and returns the plain value (shown once). */
    public function issueToken(): string
    {
        $plain = 'flk_' . Str::random(40);
        $this->forceFill(['token_hash' => hash('sha256', $plain), 'token_hint' => substr($plain, -4)])->save();

        return $plain;
    }

    public static function findByToken(?string $token): ?self
    {
        if (!$token) {
            return null;
        }

        return static::withoutGlobalScopes()->where('token_hash', hash('sha256', $token))->where('is_active', true)->first();
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(15));
    }
}
