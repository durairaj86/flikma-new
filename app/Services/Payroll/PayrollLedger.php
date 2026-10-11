<?php

namespace App\Services\Payroll;

use App\Models\Finance\Account\Account;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Payroll\LoanInstallment;
use App\Models\Payroll\PayrollRecord;
use Illuminate\Support\Facades\Auth;

/**
 * Accounting for payroll. Every payment is one balanced voucher in the ledger (finance + finance_subs),
 * linked back to the document that caused it so it can be shown on that document and removed with it.
 *
 *   Salary paid        DR Staff Salaries          net payable + loan instalment withheld
 *                      CR Cash / Bank             net payable
 *                      CR Employee Loan Receivable  loan instalment withheld
 *   Loan repaid in cash DR Cash / Bank            amount
 *                      CR Employee Loan Receivable  amount
 */
class PayrollLedger
{
    /** The company's account for a code, created under its parent when the chart does not have it yet. */
    public function account(string $code, string $type, string $name, ?string $parentCode = null): Account
    {
        $companyId = companyId() ?: null;
        $found = Account::withoutGlobalScopes()->where('code', $code)
            ->where(fn($q) => $q->whereNull('company_id')->orWhere('company_id', $companyId))
            ->orderByRaw('company_id is null')->first();
        if ($found) {
            return $found;
        }

        $parent = $parentCode ? Account::withoutGlobalScopes()->where('code', $parentCode)->where(fn($q) => $q->whereNull('company_id')->orWhere('company_id', $companyId))->first() : null;
        $account = new Account();
        $account->forceFill([
            'company_id' => $companyId,
            'name' => $name,
            'code' => $code,
            'type' => $type,
            'parent_id' => $parent?->id,
            'is_level' => $parent ? ((int) $parent->is_level + 1) : 0,
            'is_grouped' => 0,
            'is_last' => 1,
            'is_active' => 1,
        ])->save();

        return $account;
    }

    public function salaryExpenseAccount(): Account
    {
        return $this->account('5200', 'Expense', 'Staff Salaries', '5000');
    }

    public function loanReceivableAccount(): Account
    {
        return $this->account('1180', 'Asset', 'Employee Loan Receivable', '1100');
    }

    /** Post the payment of one payroll record. Replaces any earlier posting of the same record. */
    public function postPayroll(PayrollRecord $pr, int $bankAccountId, string $description): Finance
    {
        $this->removePayroll($pr);

        $net = round((float) $pr->net_payable, 2);
        $loan = round((float) $pr->loan_deduction, 2);
        $expense = $this->salaryExpenseAccount();
        $date = $pr->payment_date ?? now();

        $lines = [[$expense->id, $net + $loan, 0, $description]];
        $lines[] = [$bankAccountId, 0, $net, $description];
        if ($loan > 0) {
            $lines[] = [$this->loanReceivableAccount()->id, 0, $loan, __('Loan recovery: :no', ['no' => $pr->payroll_number])];
        }

        return $this->voucher('PV', $pr->payroll_number ?: ('PR-' . $pr->id), $date, $description, PayrollRecord::class, $pr->id, $lines);
    }

    public function removePayroll(PayrollRecord $pr): void
    {
        $this->remove(PayrollRecord::class, $pr->id);
    }

    /** An employee pays part of a loan back in cash / by transfer. */
    public function postLoanRepayment(LoanInstallment $inst): ?Finance
    {
        $amount = round((float) $inst->amount, 2);
        if ($amount <= 0 || !$inst->bank_account_id) {
            return null;
        }
        $desc = __('Loan repayment: Loan #:id', ['id' => $inst->loan_id]) . ($inst->remarks ? ' - ' . $inst->remarks : '');

        return $this->voucher('LV', 'LR-' . $inst->id, $inst->paid_date, $desc, LoanInstallment::class, $inst->id, [
            [$inst->bank_account_id, $amount, 0, $desc],
            [$this->loanReceivableAccount()->id, 0, $amount, $desc],
        ]);
    }

    public function removeLoanRepayment(LoanInstallment $inst): void
    {
        $this->remove(LoanInstallment::class, $inst->id);
    }

    /** Entries posted for a document, for display (document page). */
    public function entriesFor(string $type, int $id)
    {
        return FinanceSub::withoutGlobalScopes()->with('account:id,code,name')->where('linked_type', $type)->where('linked_id', $id)->orderBy('id')->get();
    }

    private function voucher(string $voucherType, string $voucherNo, $date, string $narration, string $linkedType, int $linkedId, array $lines): Finance
    {
        $total = round(collect($lines)->sum(fn($l) => $l[1]), 2);
        $day = \Illuminate\Support\Carbon::parse($date)->toDateString();
        $companyId = companyId() ?: null;

        $finance = new Finance();
        $finance->voucher_no = $voucherNo;
        $finance->voucher_type = $voucherType;
        $finance->reference_no = $voucherNo;
        $finance->reference_date = $day;
        $finance->narration = $narration;
        $finance->currency = 'SAR';
        $finance->exchange_rate = 1;
        $finance->total_debit = $total;
        $finance->total_credit = $total;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $total;
        $finance->base_total_credit = $total;
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $linkedId;
        $finance->linked_type = $linkedType;
        $finance->company_id = $companyId;
        $finance->user_id = Auth::id();
        $finance->save();

        FinanceSub::insert(array_map(fn($l) => [
            'finance_id' => $finance->id,
            'voucher_no' => $voucherNo,
            'voucher_type' => $voucherType,
            'reference_no' => $voucherNo,
            'account_id' => $l[0],
            'reference_date' => $day,
            'description' => $l[3],
            'debit' => $l[1],
            'credit' => $l[2],
            'currency' => 'SAR',
            'base_debit' => $l[1],
            'base_credit' => $l[2],
            'base_currency' => 'SAR',
            'exchange_rate' => 1,
            'is_tax_line' => 0,
            'is_auto_generated' => 1,
            'linked_id' => $linkedId,
            'linked_type' => $linkedType,
            'user_id' => Auth::id(),
            'company_id' => $companyId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $lines));

        return $finance;
    }

    private function remove(string $type, int $id): void
    {
        foreach (Finance::withoutGlobalScopes()->where('linked_type', $type)->where('linked_id', $id)->get() as $f) {
            FinanceSub::withoutGlobalScopes()->where('finance_id', $f->id)->delete();
            $f->delete();
        }
    }
}
