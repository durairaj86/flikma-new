<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

/**
 * Choices a company makes in Settings › Finance: what happens when a customer invoice goes over the customer's credit limit,
 * and what an emailed customer / supplier statement carries.
 */
class FinanceSettings
{
    public const CREDIT_CHECKS = [
        'off' => 'Off - no check',
        'warn' => 'Warn - show the limit, the user can still approve',
        'block' => 'Block - refuse the approval (users with the Credit override right can still approve)',
    ];

    public const STATEMENT_ATTACHMENTS = [
        'pdf' => 'PDF',
        'pdf_excel' => 'PDF and Excel',
        'excel' => 'Excel',
    ];

    public const DEFAULTS = [
        'credit_check' => 'off',
        'statement_attachment' => 'pdf',
    ];

    public static function get(string $key): string
    {
        return (string) (self::all()[$key] ?? self::DEFAULTS[$key]);
    }

    /** Every setting of the current company, defaults filled in (read once per request). */
    private static array $cache = [];

    public static function all(?int $companyId = null): array
    {
        $companyId ??= (int) companyId();
        if (!isset(self::$cache[$companyId])) {
            $saved = $companyId ? json_decode((string) DB::table('companies')->where('id', $companyId)->value('finance_settings'), true) : null;
            self::$cache[$companyId] = array_merge(self::DEFAULTS, array_intersect_key(is_array($saved) ? $saved : [], self::DEFAULTS));
        }

        return self::$cache[$companyId];
    }

    public static function save(array $values, ?int $companyId = null): void
    {
        $companyId ??= (int) companyId();
        $settings = array_intersect_key(array_merge(self::all($companyId), $values), self::DEFAULTS);
        DB::table('companies')->where('id', $companyId)->update(['finance_settings' => json_encode($settings)]);
        self::$cache[$companyId] = $settings;
    }
}
