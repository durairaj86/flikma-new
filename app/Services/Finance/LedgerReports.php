<?php

namespace App\Services\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceLedger;

/**
 * The statutory finance reports computed from the finance_ledgers view (docs/finance-ledger-design.md, phase 5).
 * Each method returns exactly the array shape the report components already render, so the screens, Excel and PDF exports
 * are unchanged. One grouped query per report instead of two or three queries per account.
 */
class LedgerReports
{
    public const INPUT_VAT = '1150';
    public const OUTPUT_VAT = '2130';
    public const PAYABLE = '2110';

    public static function enabled(): bool
    {
        return (bool) config('finance.reports.use_ledger', true);
    }

    /** Active accounts of the given types, narrowed by the screen's search box (name, code or type). */
    private static function accounts(?array $types = null, ?string $search = null)
    {
        $q = Account::where('is_active', 1);
        if (!empty($search)) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%');
            });
        }
        if ($types) {
            $q->whereIn('type', $types);
        }

        return $q;
    }

    /** base debit / credit totals per account id for the given account ids. */
    private static function totals(array $accountIds, ?string $from, string $to)
    {
        if (!$accountIds) {
            return collect();
        }
        $q = FinanceLedger::query()->actual()->whereIn('account_id', $accountIds)->where('transaction_date', '<=', $to);
        if ($from) {
            $q->where('transaction_date', '>=', $from);
        }

        return $q->groupBy('account_id')->selectRaw('account_id, SUM(base_debit) AS total_debit, SUM(base_credit) AS total_credit')->get()->keyBy('account_id');
    }

    public static function trialBalance(string $start, string $end, ?string $search = null): array
    {
        $accounts = self::accounts(null, $search)->orderBy('code')->get();
        // opening balance + period activity = everything up to the end date
        $totals = self::totals($accounts->pluck('id')->all(), null, $end);

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($accounts as $account) {
            $t = $totals->get($account->id);
            $debit = $t->total_debit ?? 0;
            $credit = $t->total_credit ?? 0;
            if ($debit > 0 || $credit > 0) {
                $rows[] = ['account_code' => $account->code, 'account_name' => $account->name, 'account_type' => $account->type, 'debit' => $debit, 'credit' => $credit];
                $totalDebit += $debit;
                $totalCredit += $credit;
            }
        }

        return ['accounts' => $rows, 'total_debit' => $totalDebit, 'total_credit' => $totalCredit];
    }

    public static function profitAndLoss(string $start, string $end, ?string $search = null): array
    {
        $accounts = self::accounts(['Income', 'Expense'], $search)->orderBy('type')->orderBy('code')->get();
        $totals = self::totals($accounts->pluck('id')->all(), $start, $end);

        $revenue = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpenses = 0;
        foreach ($accounts as $account) {
            $t = $totals->get($account->id);
            $debit = $t->total_debit ?? 0;
            $credit = $t->total_credit ?? 0;
            if ($account->type === 'Income') {
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $revenue[] = ['account_code' => $account->code, 'account_name' => $account->name, 'balance' => $balance];
                    $totalRevenue += $balance;
                }
            } else {
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $expenses[] = ['account_code' => $account->code, 'account_name' => $account->name, 'balance' => $balance];
                    $totalExpenses += $balance;
                }
            }
        }

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $totalRevenue - abs($totalExpenses),
        ];
    }

    public static function balanceSheet(string $end, ?string $search = null): array
    {
        $accounts = self::accounts(['Asset', 'Liability', 'Equity'], $search)->orderBy('type')->orderBy('code')->get();
        $totals = self::totals($accounts->pluck('id')->all(), null, $end);

        $assets = $liabilities = $equity = [];
        $totalAssets = $totalLiabilities = $totalEquity = 0;
        foreach ($accounts as $account) {
            $t = $totals->get($account->id);
            $debit = $t->total_debit ?? 0;
            $credit = $t->total_credit ?? 0;
            $balance = $account->type === 'Asset' ? $debit - $credit : $credit - $debit;
            if ($balance == 0) {
                continue;
            }
            $row = ['account_code' => $account->code, 'account_name' => $account->name, 'balance' => $balance];
            if ($account->type === 'Asset') {
                $assets[] = $row;
                $totalAssets += $balance;
            } elseif ($account->type === 'Liability') {
                $liabilities[] = $row;
                $totalLiabilities += $balance;
            } else {
                $equity[] = $row;
                $totalEquity += $balance;
            }
        }

        // current-year result (all Income minus all Expense up to the end date) is part of equity, searched or not
        $netIncome = self::netIncomeTo($end);
        if ($netIncome != 0) {
            $equity[] = ['account_code' => '', 'account_name' => 'Current Year Net Income / (Loss)', 'balance' => $netIncome];
            $totalEquity += $netIncome;
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilities + $totalEquity,
            'net_income' => $netIncome,
        ];
    }

    /** Income minus Expense from the beginning up to $end (active accounts). */
    public static function netIncomeTo(string $end): float
    {
        $ids = self::accounts(['Income'])->pluck('id')->all();
        $income = 0;
        if ($ids) {
            $d = FinanceLedger::query()->actual()->whereIn('account_id', $ids)->where('transaction_date', '<=', $end)->selectRaw('SUM(base_credit) c, SUM(base_debit) d')->first();
            $income = ($d->c ?? 0) - ($d->d ?? 0);
        }
        $ids = self::accounts(['Expense'])->pluck('id')->all();
        $expense = 0;
        if ($ids) {
            $d = FinanceLedger::query()->actual()->whereIn('account_id', $ids)->where('transaction_date', '<=', $end)->selectRaw('SUM(base_debit) d, SUM(base_credit) c')->first();
            $expense = ($d->d ?? 0) - ($d->c ?? 0);
        }

        return (float) ($income - $expense);
    }

    /** VAT lines of a period: the base for the Tax Summary, Input Tax and Output Tax reports. */
    private static function taxQuery(string $start, string $end, ?string $search = null)
    {
        $q = FinanceLedger::query()->actual()->where('is_tax_line', 1)->whereBetween('transaction_date', [$start, $end]);
        if (!empty($search)) {
            $q->where(function ($w) use ($search) {
                $w->where('reference_no', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('narration', 'like', '%' . $search . '%');
            });
        }

        return $q;
    }

    public static function taxSummary(string $start, string $end, ?string $search = null): array
    {
        $in = (clone self::taxQuery($start, $end, $search))->where('account_code', self::INPUT_VAT)->selectRaw('SUM(base_debit) d, SUM(base_credit) c')->first();
        $out = (clone self::taxQuery($start, $end, $search))->where('account_code', self::OUTPUT_VAT)->selectRaw('SUM(base_credit) c, SUM(base_debit) d')->first();
        $totalInput = ($in->d ?? 0) - ($in->c ?? 0);
        $totalOutput = ($out->c ?? 0) - ($out->d ?? 0);

        $rows = [];
        if ($totalInput != 0) {
            $rows[] = ['account_code' => '1150', 'account_name' => 'Input VAT', 'type' => 'Input Tax', 'balance' => $totalInput];
        }
        if ($totalOutput != 0) {
            $rows[] = ['account_code' => '2130', 'account_name' => 'Output VAT Payable', 'type' => 'Output Tax', 'balance' => $totalOutput];
        }

        return ['tax_accounts' => $rows, 'total_input_tax' => $totalInput, 'total_output_tax' => $totalOutput, 'net_tax' => $totalOutput - $totalInput];
    }

    /**
     * Detail lines of the Input Tax ($side 'input': debit - credit) or Output Tax ($side 'output': credit - debit) report,
     * in the document currency like the screens always did.
     */
    public static function taxLines(string $side, string $start, string $end, ?string $search = null): array
    {
        $input = $side === 'input';
        $rows = self::taxQuery($start, $end, $search)->where('account_code', $input ? self::INPUT_VAT : self::OUTPUT_VAT)
            ->orderBy('transaction_date')->orderBy('id')->get();

        $lines = [];
        $total = 0;
        foreach ($rows as $r) {
            $amount = $input ? $r->debit - $r->credit : $r->credit - $r->debit;
            if ($amount == 0) {
                continue;
            }
            $lines[] = [
                'account_code' => $input ? '1150' : '2130',
                'account_name' => $input ? 'Input VAT' : 'Output VAT Payable',
                'voucher_no' => $r->voucher_no,
                'voucher_type' => $r->voucher_type,
                'reference_no' => $r->reference_no,
                'reference_date' => $r->transaction_date?->format('d-m-Y'),   // same display format FinanceSub gave
                'description' => $r->description,
                'amount' => $amount,
            ];
            $total += $amount;
        }

        return ['lines' => $lines, 'total' => $total];
    }

    /** Header numbers of the Input / Output Tax screens: total tax and the number of tax lines. */
    public static function taxHeader(string $side, string $start, string $end): array
    {
        $input = $side === 'input';
        $q = self::taxQuery($start, $end)->where('account_code', $input ? self::INPUT_VAT : self::OUTPUT_VAT);
        $debit = (clone $q)->sum('debit');
        $credit = (clone $q)->sum('credit');

        return [
            'total' => $input ? $debit - $credit : $credit - $debit,
            'count' => (clone $q)->where($input ? 'debit' : 'credit', '>', 0)->count(),
        ];
    }

    /**
     * Supplier ledger lines: the supplier's lines on the PAYABLE account only. The earlier report took every line tagged
     * with the supplier (expense, VAT and payable together), which always netted to zero.
     */
    public static function supplierLines(int $supplierId, ?string $from, string $to)
    {
        $q = FinanceLedger::query()->actual()->where('supplier_id', $supplierId)->where('account_code', self::PAYABLE);
        $from ? $q->whereBetween('transaction_date', [$from, $to]) : $q->where('transaction_date', '<', $to);

        return $q->orderBy('transaction_date')->orderBy('id')->get();
    }
}
