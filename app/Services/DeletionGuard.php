<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One place that decides whether a record may be deleted. A record is protected when ANY live
 * (not deleted) row in ANY table still points at it — found by scanning the schema for the foreign
 * key column, so a module added later is covered without touching this class — plus a few status
 * rules (approved documents have ledger entries and can never be deleted).
 */
class DeletionGuard
{
    /** Tables that are old copies / pure link tables and must not block a delete. */
    private const IGNORE_SUFFIX = '_old';

    /** Friendly names for the "linked with …" message. */
    private const LABELS = [
        'enquiries' => 'enquiries', 'quotations' => 'quotations', 'jobs' => 'jobs', 'customer_invoices' => 'customer invoices',
        'supplier_invoices' => 'supplier invoices', 'proforma_invoices' => 'proforma invoices', 'credit_notes' => 'credit notes',
        'debit_notes' => 'debit notes', 'collections' => 'collections', 'payments' => 'payments', 'expenses' => 'expenses',
        'finance' => 'ledger entries', 'finance_subs' => 'ledger entries', 'airway_bills' => 'airway bills', 'seaway_bills' => 'seaway bills',
        'waybills' => 'waybills', 'assets' => 'assets', 'journal_vouchers' => 'journal vouchers', 'job_clearances' => 'job clearances',
        'job_tracking' => 'job tracking', 'zatca_histories' => 'ZATCA records', 'customer_portal_users' => 'portal users',
        'quotation_new_charges' => 'quotation charges', 'collection_invoices' => 'collections', 'payment_invoices' => 'payments',
        'accounts' => 'sub accounts', 'expense_subs' => 'expense lines', 'customer_invoice_subs' => 'customer invoice lines',
        'supplier_invoice_subs' => 'supplier invoice lines', 'credit_note_subs' => 'credit note lines',
        'journal_voucher_items' => 'journal voucher lines', 'payment_additional_transactions' => 'payment transactions', 'items' => 'items',
        'attendances' => 'attendance records', 'basic_salaries' => 'basic salary records', 'monthly_salaries' => 'monthly salary records',
        'employee_loans' => 'employee loans', 'customers' => 'customers', 'suppliers' => 'suppliers', 'prospects' => 'prospects',
        'waybill_subs' => 'waybill lines', 'airway_bill_subs' => 'airway bill lines', 'seaway_bill_subs' => 'seaway bill lines', 'enquiry_subs' => 'enquiry lines',
        'quotation_subs' => 'quotation lines', 'proforma_invoice_subs' => 'proforma invoice lines', 'debit_note_subs' => 'debit note lines',
        'bookings' => 'bookings', 'delivery_orders' => 'delivery orders', 'rate_sheets' => 'rate sheets', 'master_bls' => 'master B/Ls', 'arrival_notices' => 'arrival notices', 'users' => 'users created by this user', 'department_module_permissions' => 'department permissions', 'employees' => 'employee records', 'banks' => 'bank accounts', 'documents' => 'documents',
        'period_closings' => 'period closings', 'currency_locks' => 'currency locks', 'zatca_histories' => 'ZATCA records',
    ];

    /** Columns that point at a user, and tables that merely hold per-user preferences (cleaned up with the user, never a blocker). */
    private const USER_COLUMNS = ['employee_id', 'user_id', 'created_by', 'updated_by', 'approved_by', 'prepared_by', 'salesperson_id'];
    private const USER_PREFERENCE_TABLES = ['sessions', 'log_histories', 'ai_usage_logs', 'column_settings', 'user_dashboard_layouts', 'company_users',
        'personal_access_tokens', 'password_reset_tokens', 'notifications'];

    /** Ledger accounts the automatic postings use by id (receivable, payable, VAT, revenue, bank, expense, cost of sales). */
    private const SYSTEM_ACCOUNT_IDS = [4, 5, 7, 18, 20, 32, 38, 42, 47];

    /** Tables that merely mirror a link and are cleaned up with the record itself. */
    private const OWNED = [
        'enquiry_subs', 'quotation_containers', 'quotation_packages', 'quotation_charges', 'quotation_subs',
        'job_containers', 'job_packages', 'customer_invoice_subs', 'supplier_invoice_subs',
    ];

    /** @return string[] reasons the record cannot be deleted (empty = free to delete) */
    public function blockers(string $entity, int $id): array
    {
        $why = [];
        switch ($entity) {
            case 'customer':
                $why = $this->linked('customer_id', $id, ['prospects']);
                break;
            case 'supplier':
                $why = array_merge($this->linked('supplier_id', $id), $this->linked('vendor_id', $id));
                break;
            case 'account':
                // Sub accounts, posted ledger entries, invoice / expense / payment lines, items and journal lines that use this account.
                $why = array_merge(
                    $this->subAccounts($id),
                    $this->linked('account_id', $id, [], true),
                    $this->linked('account', $id, [], true),
                    $this->linked('cost_account_id', $id, [], true)
                );
                if (in_array($id, self::SYSTEM_ACCOUNT_IDS, true)) {
                    $why[] = __('it is a system account used for automatic postings');
                }
                break;
            case 'user':
                $why = $this->usedByUser($id);
                if ((int) \Illuminate\Support\Facades\Auth::id() === $id) {
                    $why[] = __('you cannot delete your own account');
                }
                break;
            case 'booking':
                // Pending or cancelled bookings only: once confirmed / shipped the carrier has a live booking.
                if (DB::table('bookings')->where('id', $id)->whereIn('status', [2, 3])->exists()) {
                    $why[] = __('only a pending or cancelled booking can be deleted');
                }
                break;
            case 'delivery_order':
                // Once it is on the road or delivered the record is the proof of delivery.
                if (DB::table('delivery_orders')->where('id', $id)->whereIn('status', [2, 3])->exists()) {
                    $why[] = __('only a pending or cancelled delivery order can be deleted');
                }
                break;
            case 'arrival_notice':
                // Once the consignee has been told the notice is part of the shipment record.
                if (DB::table('arrival_notices')->where('id', $id)->whereIn('status', [2, 3])->exists()) {
                    $why[] = __('only a draft or cancelled arrival notice can be deleted');
                }
                break;
            case 'master_bl':
                $houses = DB::table('seaway_bills')->where('master_bl_id', $id)->whereNull('deleted_at')->count()
                    + DB::table('airway_bills')->where('master_bl_id', $id)->whereNull('deleted_at')->count();
                if ($houses > 0) {
                    $why[] = __('it has :n house bills under it', ['n' => $houses]);
                }
                if (DB::table('master_bls')->where('id', $id)->whereIn('status', [2, 3])->exists()) {
                    $why[] = __('only a draft or cancelled master B/L can be deleted');
                }
                break;
            case 'description':
                $why = $this->linked('description_id', $id, [], true);
                break;
            case 'unit':
                $why = array_merge($this->linked('unit_id', $id, [], true), $this->unitNameUsed($id));
                break;
            case 'salesperson':
                $why = $this->linked('salesperson_id', $id);
                break;
            case 'bank':
                // Bank details print on invoices; nothing links to a bank row by id, so only the "last one" rule applies.
                if (DB::table('banks')->count() <= 1) {
                    $why[] = __('it is the only bank account, and invoices print its details');
                }
                break;
            case 'prospect':
                $why = $this->linked('prospect_id', $id);
                if (DB::table('prospects')->where('id', $id)->whereNotNull('customer_id')->exists()) {
                    $why[] = __('it has already been converted to a customer');
                }
                break;
            case 'enquiry':
                $why = $this->linked('enquiry_id', $id, self::OWNED);
                break;
            case 'quotation':
                $why = $this->linked('quotation_id', $id, self::OWNED);
                if (DB::table('quotations')->where('id', $id)->whereNotNull('job_id')->exists()) {
                    $why[] = __('it has been converted to a job');
                }
                if (DB::table('quotations')->where('id', $id)->where('status', '>', 1)->exists()) {
                    $why[] = __('only a pending quotation can be deleted');
                }
                break;
            case 'job':
                $why = $this->linked('job_id', $id, self::OWNED);
                break;
            case 'customer_invoice':
                $why = array_merge($this->linked('customer_invoice_id', $id, self::OWNED), $this->linked('invoice_id', $id));
                $why = array_merge($why, $this->ledger('App\\Models\\Finance\\CustomerInvoice\\CustomerInvoice', $id));
                if (DB::table('customer_invoices')->where('id', $id)->where('status', '!=', 1)->exists()) {
                    $why[] = __('only a draft invoice can be deleted');
                }
                break;
            case 'supplier_invoice':
                $why = array_merge($this->linked('supplier_invoice_id', $id, self::OWNED), $this->ledger('supplier_invoice', $id), $this->debitNotesAgainstSupplierInvoice($id));
                if (DB::table('supplier_invoices')->where('id', $id)->where('status', '!=', 1)->exists()) {
                    $why[] = __('only a draft invoice can be deleted');
                }
                break;
            case 'credit_note':
                $why = $this->ledger('App\\Models\\Finance\\Adjustment\\CreditNote', $id);
                if (DB::table('credit_notes')->where('id', $id)->where('status', '!=', 1)->exists()) {
                    $why[] = __('only a draft credit note can be deleted');
                }
                break;
            case 'debit_note':
                $why = $this->ledger('App\\Models\\Finance\\Adjustment\\DebitNote', $id);
                if (DB::table('debit_notes')->where('id', $id)->where('status', '!=', 1)->exists()) {
                    $why[] = __('only a draft debit note can be deleted');
                }
                break;
            case 'collection':
                $why = $this->ledger('App\\Models\\Finance\\Collection\\Collection', $id);
                if (DB::table('collections')->where('id', $id)->where('status', '!=', 1)->exists()) {
                    $why[] = __('only a draft collection can be deleted');
                }
                break;
            case 'payment':
                $why = $this->ledger('App\\Models\\Finance\\Payment\\Payment', $id);
                if (DB::table('payments')->where('id', $id)->where('status', '!=', 1)->exists()) {
                    $why[] = __('only a draft payment can be deleted');
                }
                break;
            case 'expense':
                $why = $this->ledger('App\\Models\\Finance\\Expense\\Expense', $id);
                if (DB::table('expenses')->where('id', $id)->where('status', '!=', 1)->exists()) {
                    $why[] = __('only a pending expense can be deleted');
                }
                break;
        }

        return array_values(array_unique($why));
    }

    /** Standard JSON reply for a blocked delete (HTTP 422 so the UI shows it as an error). */
    public function refusal(string $what, array $why): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => __('This :what cannot be deleted: :why.', ['what' => $what, 'why' => implode('; ', $why)]),
            'reasons' => $why,
        ], 422);
    }

    // ───────────────────────── internals ─────────────────────────

    /** @return string[] */
    private function linked(string $column, int $id, array $except = [], bool $includeOwned = false): array
    {
        $found = [];
        foreach ($this->tablesWith($column) as $table) {
            if (in_array($table, $except, true) || (!$includeOwned && in_array($table, self::OWNED, true))) {
                continue;
            }
            $q = DB::table($table)->where($column, $id);
            if (Schema::hasColumn($table, 'deleted_at')) {
                $q->whereNull('deleted_at');
            }
            $n = $q->count();
            if ($n > 0) {
                $label = self::LABELS[$table] ?? str_replace('_', ' ', $table);
                $found[$label] = ($found[$label] ?? 0) + $n;
            }
        }
        $out = [];
        foreach ($found as $label => $n) {
            $out[] = __('it is linked with :n :label', ['n' => $n, 'label' => __($label)]);
        }

        return $out;
    }

    /**
     * Everything the user is attached to: payroll (attendance, salaries, loans), records they created / updated /
     * approved / prepared, customers and enquiries they are salesperson of, ledger entries they posted, users they created.
     * @return string[]
     */
    private function usedByUser(int $id): array
    {
        $found = [];
        foreach (self::USER_COLUMNS as $column) {
            foreach ($this->tablesWith($column) as $table) {
                if (in_array($table, self::USER_PREFERENCE_TABLES, true)) {
                    continue;
                }
                $q = DB::table($table)->where($column, $id);
                if ($table === 'users') {
                    $q->where('id', '!=', $id); // a user's own creator column doesn't count
                }
                if (Schema::hasColumn($table, 'deleted_at')) {
                    $q->whereNull('deleted_at');
                }
                $n = $q->count();
                if ($n > 0) {
                    $label = self::LABELS[$table] ?? str_replace('_', ' ', $table);
                    $found[$label] = max($found[$label] ?? 0, $n); // same rows can match several columns: don't double count
                }
            }
        }
        $out = [];
        foreach ($found as $label => $n) {
            $out[] = __('it is linked with :n :label', ['n' => $n, 'label' => __($label)]);
        }

        return $out;
    }

    /** Removes the per-user preference rows (sessions, dashboard layout, company membership …) when the user is deleted. */
    public function clearUserPreferences(int $id): void
    {
        foreach (self::USER_PREFERENCE_TABLES as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id') && $table !== 'log_histories') {
                DB::table($table)->where('user_id', $id)->delete();
            }
        }
    }

    /** @return string[] */
    private function debitNotesAgainstSupplierInvoice(int $id): array
    {
        $n = DB::table('debit_notes')->where('invoice_id', $id)->whereNull('deleted_at')->count();

        return $n > 0 ? [__('it is linked with :n :label', ['n' => $n, 'label' => __('debit notes')])] : [];
    }

    /** Invoice / item rows store the unit by name as well as by id. */
    private function unitNameUsed(int $id): array
    {
        $name = DB::table('units')->where('id', $id)->value('unit_name');
        if (!$name) {
            return [];
        }
        $found = 0;
        foreach (['items', 'customer_invoice_subs', 'supplier_invoice_subs', 'quotation_charges', 'quotation_new_charges', 'hs_tariffs'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'unit')) {
                $q = DB::table($table)->where('unit', $name);
                if (Schema::hasColumn($table, 'deleted_at')) {
                    $q->whereNull('deleted_at');
                }
                $found += $q->count();
            }
        }

        return $found > 0 ? [__('it is linked with :n :label', ['n' => $found, 'label' => __('records that use this unit by name')])] : [];
    }

    /** @return string[] */
    private function subAccounts(int $id): array
    {
        $n = DB::table('accounts')->where('parent_id', $id)->count();

        return $n > 0 ? [__('it has :n sub accounts', ['n' => $n])] : [];
    }

    /** Approved documents leave ledger entries behind. */
    private function ledger(string $type, int $id): array
    {
        $n = DB::table('finance')->where('linked_type', $type)->where('linked_id', $id)->count();

        return $n > 0 ? [__('it has posted ledger entries')] : [];
    }

    private function tablesWith(string $column): array
    {
        static $cache = [];
        if (!isset($cache[$column])) {
            $cache[$column] = collect(DB::select(
                'select TABLE_NAME from information_schema.COLUMNS where TABLE_SCHEMA = database() and COLUMN_NAME = ?',
                [$column]
            ))->pluck('TABLE_NAME')
                ->reject(fn ($t) => str_ends_with($t, self::IGNORE_SUFFIX))
                ->values()->all();
        }

        return $cache[$column];
    }
}
