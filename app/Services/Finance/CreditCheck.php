<?php

namespace App\Services\Finance;

use App\Enums\CustomerInvoiceEnum;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Credit control on customer invoice approval (Settings › Finance › Credit limit check):
 *   - the customer's receivable now (account 1130 in the books) plus this invoice must stay within the credit limit;
 *   - no approved invoice of the customer may be open past its due date (due_at / due_date, else invoice date + credit days).
 * A limit / days of 0 or empty means "not set" and is not checked.
 */
class CreditCheck
{
    private const AR = 5;   // 1130 Accounts Receivable

    /** null when the check is off or nothing is wrong, else ['mode' => warn|block, 'message' => ..., 'over' => bool, 'overdue' => [...]] */
    public function forInvoice(CustomerInvoice $invoice): ?array
    {
        $mode = FinanceSettings::get('credit_check');
        if ($mode === 'off' || !$invoice->customer_id) {
            return null;
        }
        $customer = Customer::find($invoice->customer_id);
        if (!$customer) {
            return null;
        }
        $limit = (float) $customer->credit_limit;
        $days = (int) $customer->credit_days;
        $dec = decimals();
        $problems = [];

        $over = false;
        if ($limit > 0) {
            $outstanding = $this->outstanding($customer->id);
            $amount = (float) ($invoice->base_grand_total ?: $invoice->grand_total);
            if ($outstanding + $amount > $limit + 0.005) {
                $over = true;
                $problems[] = __('Credit limit :limit exceeded: outstanding :out + this invoice :amount = :total.', [
                    'limit' => number_format($limit, $dec), 'out' => number_format($outstanding, $dec),
                    'amount' => number_format($amount, $dec), 'total' => number_format($outstanding + $amount, $dec),
                ]);
            }
        }

        $overdue = $this->overdue($customer->id, $days, $invoice->id);
        if ($overdue) {
            $list = collect($overdue)->take(5)->map(fn ($o) => __(':no (:days days)', ['no' => $o['no'], 'days' => $o['days']]))->implode(', ');
            $problems[] = __('Overdue invoices: :list.', ['list' => $list . (count($overdue) > 5 ? ' …' : '')]);
        }

        return $problems ? [
            'mode' => $mode,
            'over' => $over,
            'overdue' => $overdue,
            'message' => __(':customer:', ['customer' => $customer->name_en]) . ' ' . implode(' ', $problems),
        ] : null;
    }

    /** Receivable of the customer in the books today (base currency). */
    public function outstanding(int $customerId): float
    {
        return round((float) DB::table('finance_ledgers')->where('company_id', companyId())->where('account_id', self::AR)
            ->where('customer_id', $customerId)->selectRaw('COALESCE(SUM(base_debit - base_credit), 0) v')->value('v'), 2);
    }

    /** Approved invoices still open after their due date: [['no' => ..., 'days' => ...]], oldest first. */
    public function overdue(int $customerId, int $creditDays, ?int $exceptInvoiceId = null): array
    {
        $today = Carbon::today();

        return CustomerInvoice::where('customer_id', $customerId)
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->when($exceptInvoiceId, fn ($q) => $q->where('id', '!=', $exceptInvoiceId))
            ->whereRaw('grand_total - COALESCE(paid_amount, 0) > 0.005')
            ->get(['id', 'row_no', 'invoice_date', 'due_date', 'due_at'])
            ->map(function ($i) use ($today, $creditDays) {
                // the due date as the customer statement reads it (due_at, then due_date), else invoice date + credit days
                $due = $i->getRawOriginal('due_at') ?: $i->getRawOriginal('due_date')
                    ?: ($creditDays > 0 ? Carbon::parse($i->getRawOriginal('invoice_date'))->addDays($creditDays)->toDateString() : null);
                return $due && Carbon::parse($due)->lt($today) ? ['no' => $i->row_no, 'days' => (int) Carbon::parse($due)->diffInDays($today)] : null;
            })
            ->filter()->sortByDesc('days')->values()->all();
    }

    /**
     * Whether the user may approve an invoice the check refuses: the Super User, or a department / user given "Credit override"
     * explicitly on the rights screen. Unlike other rights it does not fall back to the Finances section, so turning Block on
     * does not quietly let everyone with Finance rights through.
     */
    public function mayOverride($user = null): bool
    {
        $user ??= auth()->user();
        if (!$user) {
            return false;
        }
        if (isSuperUser($user)) {
            return true;
        }
        $saved = ($user->rights_mode ?? 'department') === 'manual'
            ? \App\Models\Master\UserModulePermission::effectiveFor((int) $user->id)
            : ($user->department_id ? \App\Models\Master\DepartmentModulePermission::effectiveFor((int) $user->department_id, $user->company_id ? (int) $user->company_id : null) : collect());

        return (bool) ($saved->get('finances.credit-override')->can_approve ?? false);
    }
}
