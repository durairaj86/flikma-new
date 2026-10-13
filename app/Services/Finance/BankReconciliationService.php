<?php

namespace App\Services\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\BankReconciliation\BankReconciliation;
use App\Models\Master\PeriodClosing;
use Illuminate\Support\Facades\DB;

/**
 * Bank reconciliation, kept simple:
 *   - pick the bank account, the statement date and the closing balance on the statement
 *   - tick the journal lines that appear on the statement
 *   - what the bank shows but the books do not (charges, interest) is posted as a proper balanced entry from here
 *   - when  statement balance = reconciled opening balance + ticked lines  the difference is 0 and it can be finished
 * Money in = debit on the bank account, money out = credit. Everything is in company currency.
 */
class BankReconciliationService
{
    /** Bank and cash accounts (and their sub-accounts) that can be reconciled. */
    public function accounts()
    {
        $roots = Account::whereIn('code', ['1110', '1120'])->pluck('id')->all();
        $ids = $roots;
        $queue = $roots;
        $all = Account::where('is_active', 1)->get(['id', 'parent_id']);
        while ($queue) {
            $parent = array_shift($queue);
            foreach ($all->where('parent_id', $parent) as $child) {
                $ids[] = $child->id;
                $queue[] = $child->id;
            }
        }

        return Account::where('is_active', 1)->whereIn('id', array_unique($ids))->orderBy('code')->get(['id', 'code', 'name', 'is_grouped']);
    }

    /** What the books say about the account up to a date (money in minus money out). */
    public function bookBalance(int $accountId, string $upTo): float
    {
        return round((float) DB::table('finance_ledgers')->where('company_id', companyId())->where('account_id', $accountId)
            ->where('transaction_date', '<=', $upTo)->selectRaw('COALESCE(SUM(base_debit - base_credit), 0) n')->value('n'), 2);
    }

    /** Total of the lines reconciled in earlier, completed statements of this account. */
    public function openingBalance(int $accountId): float
    {
        return round((float) DB::table('bank_reconciliation_lines as l')
            ->join('bank_reconciliations as r', 'r.id', '=', 'l.bank_reconciliation_id')
            ->join('finance_ledgers as f', 'f.id', '=', 'l.finance_sub_id')
            ->where('r.company_id', companyId())->where('r.account_id', $accountId)->where('r.status', 'completed')
            ->selectRaw('COALESCE(SUM(f.base_debit - f.base_credit), 0) n')->value('n'), 2);
    }

    /** Lines still open for this statement (up to its date, not on another statement), each flagged when ticked here. */
    public function lines(BankReconciliation $rec)
    {
        $mine = DB::table('bank_reconciliation_lines')->where('bank_reconciliation_id', $rec->id)->pluck('finance_sub_id')->flip();
        $taken = DB::table('bank_reconciliation_lines')->where('bank_reconciliation_id', '<>', $rec->id)->pluck('finance_sub_id');

        $q = DB::table('finance_ledgers')->where('company_id', $rec->company_id)->where('account_id', $rec->account_id);
        if ($rec->isCompleted()) {
            $q->whereIn('id', $mine->keys());   // a finished statement shows exactly what was ticked
        } else {
            $q->where('transaction_date', '<=', $rec->statement_date->toDateString())->whereNotIn('id', $taken);
        }

        $lines = $q->orderBy('transaction_date')->orderBy('id')->get();
        foreach ($lines as $l) {
            $l->ticked = $mine->has($l->id);   // (a plain loop: Collection::each stops when the callback returns false)
        }

        return $lines;
    }

    public function summary(BankReconciliation $rec): array
    {
        if ($rec->isCompleted()) {
            $opening = (float) $rec->opening_balance;
            $ticked = (float) $rec->cleared_balance - $opening;
            $count = (int) $rec->line_count;
        } else {
            $opening = $this->openingBalance($rec->account_id);
            $row = DB::table('bank_reconciliation_lines as l')->join('finance_ledgers as f', 'f.id', '=', 'l.finance_sub_id')
                ->where('l.bank_reconciliation_id', $rec->id)
                ->selectRaw('COALESCE(SUM(f.base_debit - f.base_credit), 0) n, COUNT(*) c')->first();
            $ticked = (float) $row->n;
            $count = (int) $row->c;
        }
        $cleared = round($opening + $ticked, 2);
        $statement = (float) $rec->statement_balance;

        return [
            'statement' => $statement,
            'opening' => round($opening, 2),
            'ticked' => round($ticked, 2),
            'ticked_count' => $count,
            'cleared' => $cleared,
            'difference' => round($statement - $cleared, 2),
            'book' => $this->bookBalance($rec->account_id, $rec->statement_date->toDateString()),
        ];
    }

    /** Tick or untick one line (only lines that are open for this statement). */
    public function tick(BankReconciliation $rec, int $lineId, bool $on): void
    {
        $this->guardDraft($rec);
        if ($on) {
            $ok = DB::table('finance_ledgers')->where('id', $lineId)->where('company_id', $rec->company_id)->where('account_id', $rec->account_id)
                ->where('transaction_date', '<=', $rec->statement_date->toDateString())->exists();
            if (!$ok) {
                throw new \RuntimeException(__('This line does not belong to this statement.'));
            }
            DB::table('bank_reconciliation_lines')->updateOrInsert(
                ['finance_sub_id' => $lineId],
                ['bank_reconciliation_id' => $rec->id, 'created_at' => now(), 'updated_at' => now()]
            );
        } else {
            DB::table('bank_reconciliation_lines')->where('bank_reconciliation_id', $rec->id)->where('finance_sub_id', $lineId)->delete();
        }
    }

    public function tickAll(BankReconciliation $rec, bool $on): void
    {
        $this->guardDraft($rec);
        foreach ($this->lines($rec) as $l) {
            $this->tick($rec, $l->id, $on);
        }
    }

    /**
     * Post what the bank shows but the books do not, as a proper balanced journal entry, and tick its bank line.
     * $kind 'out' (bank charge, fee): Dr counter account / Cr bank.   'in' (interest, other credit): Dr bank / Cr counter account.
     */
    public function addEntry(BankReconciliation $rec, string $kind, float $amount, string $date, string $description, int $counterAccountId): int
    {
        $this->guardDraft($rec);
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new \RuntimeException(__('Enter an amount greater than zero.'));
        }
        if (!in_array($kind, ['in', 'out'], true)) {
            throw new \RuntimeException(__('Choose money in or money out.'));
        }
        if ($counterAccountId === $rec->account_id) {
            throw new \RuntimeException(__('Choose a different account for the other side of the entry.'));
        }
        if (!Account::where('is_active', 1)->whereKey($counterAccountId)->exists()) {
            throw new \RuntimeException(__('The account for the other side was not found.'));
        }
        if (PeriodClosing::isLocked($date, companyId())) {
            throw new \RuntimeException(__('This date is in a closed period.'));
        }

        return DB::transaction(function () use ($rec, $kind, $amount, $date, $description, $counterAccountId) {
            $company = companyId();
            $cur = strtoupper((string) (optional(function_exists('authUserCompany') ? authUserCompany() : null)->base_currency ?: 'SAR'));
            $yy = date('y', strtotime($date));
            $last = (int) DB::table('finance')->where('company_id', $company)->where('voucher_type', 'BA')->where('voucher_no', 'like', "BA/$yy/%")
                ->selectRaw("COALESCE(MAX(CAST(SUBSTRING_INDEX(voucher_no, '/', -1) AS UNSIGNED)), 0) n")->value('n');
            $voucherNo = sprintf('BA/%s/%04d', $yy, $last + 1);
            $narration = $description !== '' ? $description : ($kind === 'out' ? __('Bank charge') : __('Bank credit'));
            $now = now();

            $financeId = DB::table('finance')->insertGetId([
                'voucher_no' => $voucherNo, 'voucher_type' => 'BA', 'reference_no' => 'REC-' . $rec->id, 'reference_date' => $date,
                'narration' => $narration, 'currency' => $cur, 'exchange_rate' => 1, 'base_currency' => $cur,
                'total_debit' => $amount, 'total_credit' => $amount, 'base_total_debit' => $amount, 'base_total_credit' => $amount,
                'is_approved' => 1, 'state' => 'posted', 'posted_at' => $date, 'period_year' => (int) date('Y', strtotime($date)), 'period_month' => (int) date('n', strtotime($date)),
                'linked_id' => $rec->id, 'linked_type' => BankReconciliation::class, 'source_type' => 'bank_reconciliation', 'source_id' => $rec->id,
                'company_id' => $company, 'user_id' => auth()->id(), 'created_at' => $now, 'updated_at' => $now,
            ]);

            $line = fn (int $account, float $dr, float $cr) => [
                'finance_id' => $financeId, 'voucher_no' => $voucherNo, 'voucher_type' => 'BA', 'reference_no' => 'REC-' . $rec->id, 'reference_date' => $date, 'entry_date' => $date,
                'account_id' => $account, 'description' => $narration, 'debit' => $dr, 'credit' => $cr, 'base_debit' => $dr, 'base_credit' => $cr,
                'currency' => $cur, 'base_currency' => $cur, 'exchange_rate' => 1, 'is_tax_line' => 0, 'is_auto_generated' => 0,
                'linked_id' => $rec->id, 'linked_type' => BankReconciliation::class, 'source_type' => 'bank_reconciliation', 'source_id' => $rec->id,
                'user_id' => auth()->id(), 'company_id' => $company, 'created_at' => $now, 'updated_at' => $now,
            ];
            $bankLine = $kind === 'out' ? $line($rec->account_id, 0, $amount) : $line($rec->account_id, $amount, 0);
            $otherLine = $kind === 'out' ? $line($counterAccountId, $amount, 0) : $line($counterAccountId, 0, $amount);
            $bankLineId = DB::table('finance_subs')->insertGetId($bankLine);
            DB::table('finance_subs')->insert($otherLine);

            // it is on the statement, so it is ticked straight away (a statement date earlier than the entry would hide it)
            DB::table('bank_reconciliation_lines')->insert(['bank_reconciliation_id' => $rec->id, 'finance_sub_id' => $bankLineId, 'created_at' => $now, 'updated_at' => $now]);

            return $financeId;
        });
    }

    /** Finish: only when the difference is 0. The figures are fixed on the statement from here on. */
    public function complete(BankReconciliation $rec): void
    {
        $this->guardDraft($rec);
        $s = $this->summary($rec);
        if (abs($s['difference']) >= 0.005) {
            throw new \RuntimeException(__('The difference must be 0.00 before you can finish.'));
        }
        $rec->update([
            'status' => 'completed', 'opening_balance' => $s['opening'], 'cleared_balance' => $s['cleared'], 'difference' => 0,
            'line_count' => $s['ticked_count'], 'completed_at' => now(), 'completed_by' => auth()->id(),
        ]);
    }

    /** Take back a finished statement (only the latest of the account): its lines become open again. */
    public function undo(BankReconciliation $rec): void
    {
        if (!$rec->isCompleted()) {
            throw new \RuntimeException(__('This statement is not finished.'));
        }
        $later = BankReconciliation::where('account_id', $rec->account_id)->where('status', 'completed')->where('id', '>', $rec->id)->exists();
        if ($later) {
            throw new \RuntimeException(__('Undo the later reconciliation of this account first.'));
        }
        $rec->delete();   // its ticked lines go with it (cascade)
    }

    private function guardDraft(BankReconciliation $rec): void
    {
        if ($rec->isCompleted()) {
            throw new \RuntimeException(__('This reconciliation is finished. Undo it to change it.'));
        }
    }
}
