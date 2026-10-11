<?php

namespace App\Services\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Str;

/**
 * The one place that writes opening balance vouchers (voucher type OB), for the manual form, the import,
 * edit and delete. Every OB voucher is always balanced; a difference caused by an edit or delete is carried
 * by one automatic "balancing" line (is_auto_generated = 1) on the Owner Capital account, so the books never go out of balance.
 */
class OpeningBalanceService
{
    public const BALANCING_NOTE = 'Opening balance - balancing entry';

    public function arId(): int { return (int) (Account::where('code', '1130')->value('id') ?: 5); }

    public function apId(): int { return (int) (Account::where('code', '2110')->value('id') ?: 18); }

    /** Account that carries automatic balancing differences. */
    public function equityId(): ?int { return Account::where('code', '3100')->value('id') ?: null; }

    public function baseCurrency(): string
    {
        $c = function_exists('authUserCompany') ? authUserCompany() : null;

        return strtoupper((string) ($c?->base_currency ?: $c?->currency ?: 'SAR'));
    }

    /** Date of the opening balances already entered (all of them share one date), or null. */
    public function lockedDate(): ?string
    {
        $f = FinanceSub::where('voucher_type', 'OB')->where('company_id', companyId())->with('finance')->first()?->finance;

        return $f?->posted_at ? \Carbon\Carbon::parse($f->posted_at)->format('Y-m-d') : null;
    }

    /** Accounts that can take an opening balance directly (posting accounts, not the receivable / payable control accounts). */
    public function isDirectAccount(int $accountId): bool
    {
        return !in_array($accountId, [$this->arId(), $this->apId()], true) && Account::where('is_last', 1)->whereKey($accountId)->exists();
    }

    /**
     * Post one balanced OB voucher.
     * $lines: account_id, customer_id, supplier_id, debit, credit, description, auto (bool, optional)
     */
    public function post(string $date, array $lines, string $narration = 'Opening Balance'): Finance
    {
        $debit = round(array_sum(array_column($lines, 'debit')), 2);
        $credit = round(array_sum(array_column($lines, 'credit')), 2);
        if ($debit !== $credit) {
            throw new \RuntimeException(sprintf('Opening balance entries must balance: debit %s, credit %s.', number_format($debit, 2), number_format($credit, 2)));
        }
        $cur = $this->baseCurrency();
        $voucherNo = 'OB-' . date('YmdHis') . '-' . strtoupper(Str::random(3));

        $finance = Finance::create([
            'voucher_no' => $voucherNo, 'voucher_type' => 'OB', 'posted_at' => $date, 'reference_date' => $date, 'narration' => $narration,
            'currency' => $cur, 'exchange_rate' => 1, 'base_currency' => $cur,
            'total_debit' => $debit, 'total_credit' => $credit, 'base_total_debit' => $debit, 'base_total_credit' => $credit,
            'is_approved' => 1, 'company_id' => companyId(), 'user_id' => auth()->id(),
        ]);
        foreach ($lines as $l) {
            FinanceSub::create([
                'finance_id' => $finance->id, 'voucher_no' => $voucherNo, 'voucher_type' => 'OB', 'account_id' => $l['account_id'],
                'customer_id' => $l['customer_id'] ?? null, 'supplier_id' => $l['supplier_id'] ?? null, 'reference_date' => $date,
                'currency' => $cur, 'base_currency' => $cur, 'exchange_rate' => 1,
                'debit' => $l['debit'], 'credit' => $l['credit'], 'base_debit' => $l['debit'], 'base_credit' => $l['credit'],
                'description' => $l['description'] ?? null, 'is_auto_generated' => !empty($l['auto']) ? 1 : 0,
                'company_id' => companyId(), 'user_id' => auth()->id(),
            ]);
        }

        return $finance;
    }

    /**
     * After an edit or delete: make the voucher balance again with the automatic balancing line, refresh the
     * header totals, and remove the voucher when nothing but the balancing line is left.
     * Returns an error message when it cannot be balanced, otherwise null.
     */
    public function rebalance(int $financeId): ?string
    {
        $subs = FinanceSub::where('finance_id', $financeId)->get();
        $own = $subs->where('is_auto_generated', 0);
        $auto = $subs->firstWhere('is_auto_generated', 1);

        if ($own->isEmpty()) {
            FinanceSub::where('finance_id', $financeId)->delete();
            Finance::whereKey($financeId)->delete();

            return null;
        }
        $diff = round($own->sum('debit') - $own->sum('credit'), 2);
        if ($diff == 0.0) {
            $auto?->delete();
        } else {
            $accountId = $auto?->account_id ?: $this->equityId();
            if (!$accountId) {
                return __('The Owner Capital account (3100) is needed to carry the difference, but it was not found.');
            }
            $row = [
                'account_id' => $accountId, 'debit' => $diff < 0 ? abs($diff) : 0, 'credit' => $diff > 0 ? $diff : 0,
                'base_debit' => $diff < 0 ? abs($diff) : 0, 'base_credit' => $diff > 0 ? $diff : 0, 'description' => self::BALANCING_NOTE, 'is_auto_generated' => 1,
            ];
            if ($auto) {
                $auto->update($row);
            } else {
                $f = Finance::find($financeId);
                $cur = $this->baseCurrency();
                FinanceSub::create($row + [
                    'finance_id' => $financeId, 'voucher_no' => $f->voucher_no, 'voucher_type' => 'OB', 'reference_date' => $f->posted_at,
                    'currency' => $cur, 'base_currency' => $cur, 'exchange_rate' => 1, 'company_id' => companyId(), 'user_id' => auth()->id(),
                ]);
            }
        }
        $all = FinanceSub::where('finance_id', $financeId);
        Finance::whereKey($financeId)->update([
            'total_debit' => $all->sum('debit'), 'total_credit' => $all->sum('credit'),
            'base_total_debit' => $all->sum('base_debit'), 'base_total_credit' => $all->sum('base_credit'),
        ]);

        return null;
    }
}
