<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only integrity checks of the ledger (finance + finance_subs). Changes nothing.
 * Run before and after every step of the finance redesign (docs/finance-ledger-design.md, section 9).
 */
class FinanceReconcile extends Command
{
    protected $signature = 'finance:reconcile {--company= : Only this company id}';

    protected $description = 'Check that the ledger balances and that headers, lines and control accounts agree (read-only)';

    private int $failed = 0;

    public function handle(): int
    {
        $co = $this->option('company');
        $scope = fn ($q, $col = 'company_id') => $co ? $q->where($col, $co) : $q;

        // 1. every entry balances (base currency)
        $bad = $scope(DB::table('finance_subs'))->select('finance_id', DB::raw('round(sum(base_debit),2) d'), DB::raw('round(sum(base_credit),2) c'))
            ->groupBy('finance_id')->havingRaw('d <> c')->get();
        $this->check('1. Every entry balances (debit = credit)', $bad->count(), $bad->take(5)->map(fn ($r) => "finance #{$r->finance_id}: {$r->d} vs {$r->c}")->all());

        // 2. header totals equal the sum of its lines
        $bad = $scope(DB::table('finance as f'), 'f.company_id')
            ->join(DB::raw('(select finance_id, round(sum(base_debit),2) d, round(sum(base_credit),2) c from finance_subs group by finance_id) s'), 's.finance_id', '=', 'f.id')
            ->whereRaw('s.d <> round(f.base_total_debit,2) or s.c <> round(f.base_total_credit,2)')->pluck('f.id');
        $this->check('2. Header totals equal the sum of their lines', $bad->count(), $bad->take(5)->map(fn ($i) => "finance #$i")->all());

        // 3. orphans and missing tenant
        $n = $scope(DB::table('finance_subs as s'), 's.company_id')->leftJoin('finance as f', 'f.id', '=', 's.finance_id')->whereNull('f.id')->count();
        $this->check('3a. Lines without a header', $n);
        $n = DB::table('finance_subs')->whereNull('company_id')->count() + DB::table('finance')->whereNull('company_id')->count();
        $this->check('3b. Rows without company_id', $n);
        $n = DB::table('finance_subs as s')->join('finance as f', 'f.id', '=', 's.finance_id')->whereColumn('s.company_id', '<>', 'f.company_id')->count();
        $this->check('3c. Line company differs from header company', $n);

        // 4. duplicate voucher numbers (needed before a unique index)
        $n = $scope(DB::table('finance'))->select('company_id', 'voucher_type', 'voucher_no')->groupBy('company_id', 'voucher_type', 'voucher_no')->havingRaw('count(*) > 1')->get()->count();
        $this->check('4. Duplicate voucher numbers', $n);

        // 5. receivable / payable control accounts equal the sum of their partner lines
        foreach ([['1130', 'customer_id', 'Receivable'], ['2110', 'supplier_id', 'Payable']] as [$code, $col, $label]) {
            $ids = DB::table('accounts')->where('code', $code)->pluck('id');
            $q = $scope(DB::table('finance_subs as s'), 's.company_id')->join('finance as f', 'f.id', '=', 's.finance_id')
                ->where('f.is_approved', 1)->whereIn('s.account_id', $ids);
            $total = round((float) (clone $q)->selectRaw('sum(s.base_debit - s.base_credit) t')->value('t'), 2);
            $byPartner = round((float) (clone $q)->whereNotNull("s.$col")->selectRaw('sum(s.base_debit - s.base_credit) t')->value('t'), 2);
            $this->check("5. $label control account ($code) = sum of lines tagged to a $col", $total === $byPartner ? 0 : 1, ["account $total vs partner lines $byPartner"]);
        }

        // 6. approved header with lines on both sides, no zero-value lines
        $n = $scope(DB::table('finance_subs'))->whereRaw('coalesce(base_debit,0) = 0 and coalesce(base_credit,0) = 0')->count();
        $this->line("   info: lines with zero amount: $n");

        // 7. new columns (after phase 1): tax lines have a rate, reconcilable lines exist
        if (Schema::hasColumn('finance_subs', 'is_reconcilable')) {
            $n = $scope(DB::table('finance_subs'))->where('is_tax_line', 1)->whereNull('tax_rate')->count();
            $this->check('7a. Tax lines without tax rate', $n);
            $n = $scope(DB::table('finance_subs'))->where('is_tax_line', 1)->whereNull('tax_type')->count();
            $this->check('7b. Tax lines without tax type (input/output)', $n);
            $n = $scope(DB::table('finance_subs'))->whereNull('entry_date')->count();
            $this->check('7c. Lines without entry_date', $n);
            $n = $scope(DB::table('finance'))->whereNull('source_type')->count();
            $this->check('7d. Entries without source_type', $n);
            $n = $scope(DB::table('finance_subs'))->where('is_reconcilable', 1)->whereNull('partner_id')->count();
            $this->check('7e. Receivable/payable lines without a partner', $n);
            $mixed = $scope(DB::table('finance_subs'))->where('is_tax_line', 1)->select('finance_id')->groupBy('finance_id')->havingRaw('count(distinct tax_rate) > 1')->get()->count();
            $this->line("   info: entries with more than one tax rate (taxable base is approximate there): $mixed");
        }

        // 8. settlements (phase 2)
        if (Schema::hasTable('finance_allocations')) {
            $n = $scope(DB::table('finance_allocations as a'), 'a.company_id')->join('finance_subs as l', 'l.id', '=', 'a.settled_line_id')
                ->where('a.status', 'active')->groupBy('a.settled_line_id', 'l.debit', 'l.credit')
                ->havingRaw('sum(a.amount) > greatest(l.debit, l.credit) + 0.005')->select('a.settled_line_id')->get()->count();
            $this->check('8a. A line is settled for more than its amount', $n);

            $n = DB::table('finance_allocations as a')->join('finance_subs as l', 'l.id', '=', 'a.settled_line_id')->join('finance_subs as m', 'm.id', '=', 'a.settling_line_id')
                ->whereRaw('l.partner_id <> a.partner_id or m.partner_id <> a.partner_id or l.account_id <> m.account_id')->count();
            $this->check('8b. Settled and settling line differ in partner or account', $n);

            foreach ([['customer_invoices', 'customer_invoice'], ['supplier_invoices', 'supplier_invoice']] as [$tbl, $type]) {
                $rows = $scope(DB::table("$tbl as i"), 'i.company_id')->whereNull('i.deleted_at')
                    ->leftJoin(DB::raw("(select settled_doc_id, sum(amount) amt from finance_allocations where settled_doc_type = '$type' and status = 'active' group by settled_doc_id) al"), 'al.settled_doc_id', '=', 'i.id')
                    ->whereRaw('round(coalesce(i.paid_amount,0),2) <> round(coalesce(al.amt,0),2)')
                    ->select('i.id', 'i.paid_amount', 'al.amt')->get();
                $this->check("8c. $type: cached paid_amount equals the allocations", $rows->count(), $rows->take(5)->map(fn ($r) => "#{$r->id}: paid_amount {$r->paid_amount} vs allocations " . ($r->amt ?? 0))->all());
            }
        }

        // 9. the finance_ledgers view agrees with the journal (phase 3)
        if (Schema::hasView('finance_ledgers')) {
            $viewTotals = $scope(DB::table('finance_ledgers'))->selectRaw('round(sum(base_debit),2) d, round(sum(base_credit),2) c, count(*) n')->first();
            $journal = $scope(DB::table('finance_subs as s'), 's.company_id')->join('finance as f', 'f.id', '=', 's.finance_id')->where('f.is_approved', 1)
                ->selectRaw('round(sum(s.base_debit),2) d, round(sum(s.base_credit),2) c, count(*) n')->first();
            $this->check('9a. View line count and totals equal the approved journal', ($viewTotals->n == $journal->n && $viewTotals->d == $journal->d && $viewTotals->c == $journal->c) ? 0 : 1,
                ["view {$viewTotals->n} lines {$viewTotals->d}/{$viewTotals->c} vs journal {$journal->n} lines {$journal->d}/{$journal->c}"]);

            $perAccount = $scope(DB::table('finance_ledgers'))->groupBy('account_id')->selectRaw('account_id, round(sum(base_debit),2) d, round(sum(base_credit),2) c')->get()->keyBy('account_id');
            $old = $scope(DB::table('finance_subs as s'), 's.company_id')->join('finance as f', 'f.id', '=', 's.finance_id')->where('f.is_approved', 1)
                ->groupBy('s.account_id')->selectRaw('s.account_id, round(sum(s.base_debit),2) d, round(sum(s.base_credit),2) c')->get()->keyBy('account_id');
            $diff = $old->filter(fn ($r, $id) => !isset($perAccount[$id]) || $perAccount[$id]->d != $r->d || $perAccount[$id]->c != $r->c);
            $this->check('9b. Per-account totals in the view equal the totals the current reports compute', $diff->count(), $diff->take(5)->map(fn ($r, $id) => "account $id")->all());

            $n = $scope(DB::table('finance_ledgers'))->whereNull('account_code')->count();
            $this->check('9c. Ledger lines whose account no longer exists (dropped by the view join)', $old->count() === $perAccount->count() ? 0 : 1);
        }

        $this->newLine();
        $this->line($this->failed === 0 ? '<info>All checks passed.</info>' : "<error>{$this->failed} check(s) failed.</error>");

        return $this->failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function check(string $label, int $count, array $examples = []): void
    {
        if ($count === 0) {
            $this->line("<info>PASS</info> $label");

            return;
        }
        $this->failed++;
        $this->line("<error>FAIL</error> $label ($count)");
        foreach ($examples as $e) {
            $this->line("       $e");
        }
    }
}
