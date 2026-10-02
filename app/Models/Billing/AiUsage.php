<?php

namespace App\Models\Billing;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;

class AiUsage extends Model
{
    use CompanyScopeTrait;

    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'company_id', 'user_id', 'feature',
        'input_tokens', 'output_tokens', 'total_tokens',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /** Token totals for the current company, optionally narrowed to a window. */
    public static function totalsForCompany(int $companyId, ?string $since = null): array
    {
        $base = static::query()->where('company_id', $companyId);

        if ($since) {
            $base->where('created_at', '>=', $since);
        }

        return [
            'input_tokens' => (clone $base)->sum('input_tokens'),
            'output_tokens' => (clone $base)->sum('output_tokens'),
            'total_tokens' => (clone $base)->sum('total_tokens'),
            'requests' => (clone $base)->count(),
        ];
    }
}
