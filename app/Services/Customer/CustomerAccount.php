<?php

namespace App\Services\Customer;

use App\Enums\CustomerInvoiceEnum;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Traits\Finance\AgingAsOf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A customer's money figures, shared by the staff customer page and the customer portal so both always agree:
 *  - Balance Due = Accounts Receivable ledger balance (same source as the Statement);
 *  - open invoices = approved invoices not yet settled (as of today).
 * Works on the current company (companyId()).
 */
class CustomerAccount
{
    use AgingAsOf;

    /** @return array<int,float> customer id => AR ledger balance */
    public function ledgerBalances(): array
    {
        return DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.company_id', companyId())
            ->whereNotNull('fs.customer_id')
            ->whereIn('fs.account_id', $this->arAccountIds())
            ->where('f.is_approved', 1)
            ->groupBy('fs.customer_id')
            ->selectRaw('fs.customer_id, SUM(fs.base_debit - fs.base_credit) as bal')
            ->pluck('bal', 'customer_id')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }

    public function balanceDue(Customer $customer): float
    {
        return (float) DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.company_id', companyId())
            ->where('fs.customer_id', $customer->id)
            ->whereIn('fs.account_id', $this->arAccountIds())
            ->where('f.is_approved', 1)
            ->sum(DB::raw('fs.base_debit - fs.base_credit'));
    }

    public function arAccountIds(): array
    {
        return DB::table('accounts')->where('code', '1130')->pluck('id')->all() ?: [5];
    }

    public function openInvoices(Customer $customer): array
    {
        $today = Carbon::today();
        $settled = $this->settledAsOf('customer', $today);
        $rows = [];
        foreach (CustomerInvoice::where('customer_id', $customer->id)
                     ->where('status', CustomerInvoiceEnum::APPROVED->value)
                     ->orderBy('invoice_date')->get() as $inv) {
            $balance = round((float) $inv->grand_total - ($settled[$inv->id] ?? 0.0), 2);
            if ($balance <= 0) {
                continue;
            }
            $due = Carbon::parse($inv->due_at ?? $inv->due_date ?? $inv->invoice_date);
            $rows[] = [
                'id' => $inv->id,
                'no' => $inv->row_no,
                'date' => Carbon::parse($inv->invoice_date),
                'due' => $due,
                'total' => (float) $inv->grand_total,
                'balance' => $balance,
                // the same open amount in the company currency (at the invoice's booked rate): what the ledger and the aging buckets are in
                'currency' => strtoupper((string) $inv->currency),
                'balance_base' => (float) $inv->grand_total > 0 ? round($balance * ((float) $inv->base_grand_total / (float) $inv->grand_total), 2) : $balance,
                'days' => (int) $due->diffInDays($today, false),
            ];
        }

        return $rows;
    }

    /** Ledger lines (company currency) with a running balance, oldest first. */
    public function statement(Customer $customer, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $entries = DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.company_id', companyId())
            ->where('fs.customer_id', $customer->id)
            ->whereIn('fs.account_id', $this->arAccountIds())
            ->where('f.is_approved', 1)
            ->when($to, fn ($q) => $q->whereDate('fs.reference_date', '<=', $to))
            ->orderBy('fs.reference_date')->orderBy('fs.id')
            ->get(['fs.reference_date', 'fs.reference_no', 'fs.voucher_no', 'f.narration', 'fs.base_debit', 'fs.base_credit']);

        $running = 0.0;
        $opening = 0.0;
        $rows = [];
        foreach ($entries as $e) {
            $running += $e->base_debit - $e->base_credit;
            $date = Carbon::parse($e->reference_date);
            if ($from && $date->lt($from)) {
                $opening = round($running, 2);
                continue;
            }
            $rows[] = [
                'date' => $date,
                'details' => $e->reference_no ?: ($e->voucher_no ?: $e->narration),
                'debit' => (float) $e->base_debit,
                'credit' => (float) $e->base_credit,
                'balance' => round($running, 2),
            ];
        }

        return ['opening' => $opening, 'rows' => $rows, 'closing' => round($running, 2)];
    }
}
