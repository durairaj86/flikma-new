<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/** Platform-wide key/value settings edited in /admin/settings. Secrets are stored encrypted. */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /** keys whose value is encrypted at rest */
    public const SECRET = ['mail_password', 'stripe_secret', 'paypal_secret'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? static::decode($key, $setting->value) : $default;
    }

    public static function set(string $key, $value, string $group = 'general'): self
    {
        if (in_array($key, self::SECRET, true) && filled($value)) {
            $value = Crypt::encryptString((string) $value);
        }

        return static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    public static function getGroup(string $group)
    {
        return static::where('group', $group)->pluck('value', 'key')->map(fn ($v, $k) => static::decode($k, $v));
    }

    private static function decode(string $key, $value)
    {
        if (!in_array($key, self::SECRET, true) || blank($value)) {
            return $value;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
