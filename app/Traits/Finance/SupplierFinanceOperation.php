<?php

namespace App\Traits\Finance;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SupplierFinanceOperation
{
    /**
     * ===============================
     * SUPPLIER INVOICE FINANCE
     * ===============================
     */
    public function storeSupplierInvoiceFinance($supplier, array $supplierSub)
    {
        try {
            DB::beginTransaction();

            // Remove existing entries if re-posting
            $this->deleteSupplierFinanceByRef($supplier->invoice_number, 'SI');

            // Ensure base totals are correct
            $baseTotalDebit = ($supplier->base_sub_total ?? 0) + ($supplier->base_tax_total ?? 0);
            $baseTotalCredit = $baseTotalDebit;

            // 1️⃣ Finance Header
            $financeId = DB::table('finance')->insertGetId([
                'voucher_no' => $supplier->row_no,
                'voucher_type' => 'SI',
                'reference_no' => $supplier->invoice_number,
                'reference_date' => formDate($supplier->invoice_date),
                'supplier_id' => $supplier->supplier_id,
                'narration' => 'Supplier Invoice: ' . $supplier->invoice_number,
                'total_debit' => $supplier->grand_total ?? 0,
                'total_credit' => $supplier->grand_total ?? 0,
                'currency' => $supplier->currency ?? 'SAR',
                'exchange_rate' => $supplier->currency_rate ?? 1,
                'base_total_debit' => $baseTotalDebit,
                'base_total_credit' => $baseTotalCredit,
                'is_approved' => 1,
                'job_id' => $supplier->job_id ?? null,
                'job_no' => $supplier->job_no ?? null,
                'posted_at' => formDate($supplier->posted_at),
                'linked_id' => $supplier->id ?? null,
                'linked_type' => 'supplier_invoice',
                'company_id' => $supplier->company_id,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2️⃣ Sub Entries
            $financeSubs = [];
            $userId = Auth::id();

            foreach ($supplierSub as $line) {
                $amount = $line['total'] ?? 0;
                $baseAmount = $amount * ($supplier->currency_rate ?? 1);

                $financeSubs[] = [
                    'finance_id' => $financeId,
                    'account_id' => $line['account_id'],
                    'voucher_no' => $supplier->row_no,
                    'voucher_type' => 'SI',
                    'reference_no' => $supplier->invoice_number,
                    'reference_date' => formDate($supplier->invoice_date),
                    'description' => $line['description'] ?? 'Supplier Invoice Line',
                    'debit' => $amount,
                    'credit' => 0,
                    'base_debit' => $baseAmount,
                    'base_credit' => 0,
                    'currency' => $supplier->currency ?? 'SAR',
                    'exchange_rate' => $supplier->currency_rate ?? 1,
                    'supplier_id' => $supplier->supplier_id,
                    'company_id' => $supplier->company_id,
                    'user_id' => $userId,
                    'linked_id' => $supplier->id ?? null,
                    'linked_type' => 'supplier_invoice',
                    'is_tax_line' => 0,
                    'job_id' => $supplier->job_id ?? null,
                    'job_no' => $supplier->job_no ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Input VAT Line
            if (($supplier->tax_total ?? 0) > 0) {
                $financeSubs[] = [
                    'finance_id' => $financeId,
                    'voucher_no' => $supplier->row_no,
                    'voucher_type' => 'SI',
                    'reference_no' => $supplier->invoice_number,
                    'account_id' => 7, //2205 Input VAT Account
                    'reference_date' => formDate($supplier->invoice_date),
                    'description' => 'Input VAT',
                    'debit' => $supplier->tax_total,
                    'credit' => 0,
                    'base_debit' => $supplier->base_tax_total ?? ($supplier->tax_total * ($supplier->currency_rate ?? 1)),
                    'base_credit' => 0,
                    'currency' => $supplier->currency ?? 'SAR',
                    'exchange_rate' => $supplier->currency_rate ?? 1,
                    'supplier_id' => $supplier->supplier_id,
                    'company_id' => $supplier->company_id,
                    'user_id' => $userId,
                    'linked_id' => $supplier->id ?? null,
                    'linked_type' => 'supplier_invoice',
                    'is_tax_line' => 1,
                    'job_id' => $supplier->job_id ?? null,
                    'job_no' => $supplier->job_no ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Accounts Payable Credit Line
            $financeSubs[] = [
                'finance_id' => $financeId,
                'voucher_no' => $supplier->row_no,
                'voucher_type' => 'SI',
                'reference_no' => $supplier->invoice_number,
                'account_id' => 18, //2110 Accounts Payable
                'reference_date' => formDate($supplier->invoice_date),
                'description' => 'Accounts Payable - Supplier',
                'debit' => 0,
                'credit' => $supplier->grand_total ?? 0,
                'base_debit' => 0,
                'base_credit' => ($supplier->grand_total ?? 0) * ($supplier->currency_rate ?? 1),
                'currency' => $supplier->currency ?? 'SAR',
                'exchange_rate' => $supplier->currency_rate ?? 1,
                'supplier_id' => $supplier->supplier_id,
                'company_id' => $supplier->company_id,
                'user_id' => $userId,
                'linked_id' => $supplier->id ?? null,
                'linked_type' => 'supplier_invoice',
                'is_tax_line' => 0,
                'job_id' => $supplier->job_id ?? null,
                'job_no' => $supplier->job_no ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('finance_sub')->insert($financeSubs);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('SupplierFinanceOperation (Invoice) error: ' . $ex->getMessage());
        }
    }

    /**
     * ===============================
     * SUPPLIER ADVANCE PAYMENT
     * ===============================
     */
    public function storeSupplierAdvanceFinance($advance)
    {
        try {
            DB::beginTransaction();

            $this->deleteSupplierFinanceByRef($advance->reference_no, 'SA');

            $financeId = DB::table('finance')->insertGetId([
                'voucher_no' => $advance->row_no,
                'voucher_type' => 'SA',
                'reference_no' => $advance->reference_no,
                'reference_date' => formDate($advance->payment_date),
                'supplier_id' => $advance->supplier_id,
                'narration' => 'Supplier Advance Payment',
                'total_debit' => $advance->amount,
                'total_credit' => $advance->amount,
                'currency' => $advance->currency ?? 'SAR',
                'exchange_rate' => $advance->exchange_rate ?? 1,
                'base_total_debit' => $advance->base_amount ?? ($advance->amount * ($advance->exchange_rate ?? 1)),
                'base_total_credit' => $advance->base_amount ?? ($advance->amount * ($advance->exchange_rate ?? 1)),
                'is_approved' => 1,
                'posted_at' => now(),
                'company_id' => $advance->company_id,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userId = Auth::id();

            DB::table('finance_sub')->insert([
                [
                    'finance_id' => $financeId,
                    'account_id' => 1300, // Advance to Supplier
                    'description' => 'Supplier Advance',
                    'debit' => $advance->amount,
                    'credit' => 0,
                    'base_debit' => $advance->base_amount ?? ($advance->amount * ($advance->exchange_rate ?? 1)),
                    'base_credit' => 0,
                    'currency' => $advance->currency ?? 'SAR',
                    'exchange_rate' => $advance->exchange_rate ?? 1,
                    'supplier_id' => $advance->supplier_id,
                    'company_id' => $advance->company_id,
                    'user_id' => $userId,
                    'linked_id' => $advance->id ?? null,
                    'linked_type' => 'supplier_advance',
                    'is_tax_line' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'finance_id' => $financeId,
                    'account_id' => $advance->bank_account_id,
                    'description' => 'Bank / Cash',
                    'debit' => 0,
                    'credit' => $advance->amount,
                    'base_debit' => 0,
                    'base_credit' => $advance->base_amount ?? ($advance->amount * ($advance->exchange_rate ?? 1)),
                    'currency' => $advance->currency ?? 'SAR',
                    'exchange_rate' => $advance->exchange_rate ?? 1,
                    'supplier_id' => $advance->supplier_id,
                    'company_id' => $advance->company_id,
                    'user_id' => $userId,
                    'linked_id' => $advance->id ?? null,
                    'linked_type' => 'supplier_advance',
                    'is_tax_line' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('SupplierFinanceOperation (Advance) error: ' . $ex->getMessage());
        }
    }

    /**
     * ===============================
     * SUPPLIER ADVANCE ADJUSTMENT
     * ===============================
     */
    public function storeSupplierAdvanceAdjustmentFinance($adjustment)
    {
        try {
            DB::beginTransaction();

            $this->deleteSupplierFinanceByRef($adjustment->reference_no, 'SAA');

            $financeId = DB::table('finance')->insertGetId([
                'voucher_no' => $adjustment->row_no,
                'voucher_type' => 'SAA',
                'reference_no' => $adjustment->reference_no,
                'reference_date' => formDate($adjustment->payment_date),
                'supplier_id' => $adjustment->supplier_id,
                'narration' => 'Supplier Advance Adjustment',
                'total_debit' => $adjustment->amount,
                'total_credit' => $adjustment->amount,
                'currency' => $adjustment->currency ?? 'SAR',
                'exchange_rate' => $adjustment->exchange_rate ?? 1,
                'base_total_debit' => $adjustment->base_amount ?? ($adjustment->amount * ($adjustment->exchange_rate ?? 1)),
                'base_total_credit' => $adjustment->base_amount ?? ($adjustment->amount * ($adjustment->exchange_rate ?? 1)),
                'is_approved' => 1,
                'posted_at' => now(),
                'company_id' => $adjustment->company_id,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userId = Auth::id();

            DB::table('finance_sub')->insert([
                [
                    'finance_id' => $financeId,
                    'account_id' => 2100, // Accounts Payable
                    'description' => 'Advance Adjustment - AP',
                    'debit' => $adjustment->amount,
                    'credit' => 0,
                    'base_debit' => $adjustment->base_amount ?? ($adjustment->amount * ($adjustment->exchange_rate ?? 1)),
                    'base_credit' => 0,
                    'currency' => $adjustment->currency ?? 'SAR',
                    'exchange_rate' => $adjustment->exchange_rate ?? 1,
                    'supplier_id' => $adjustment->supplier_id,
                    'company_id' => $adjustment->company_id,
                    'user_id' => $userId,
                    'linked_id' => $adjustment->id ?? null,
                    'linked_type' => 'supplier_advance_adjustment',
                    'is_tax_line' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'finance_id' => $financeId,
                    'account_id' => 1300, // Advance to Supplier
                    'description' => 'Advance Adjustment - Prepaid',
                    'debit' => 0,
                    'credit' => $adjustment->amount,
                    'base_debit' => 0,
                    'base_credit' => $adjustment->base_amount ?? ($adjustment->amount * ($adjustment->exchange_rate ?? 1)),
                    'currency' => $adjustment->currency ?? 'SAR',
                    'exchange_rate' => $adjustment->exchange_rate ?? 1,
                    'supplier_id' => $adjustment->supplier_id,
                    'company_id' => $adjustment->company_id,
                    'user_id' => $userId,
                    'linked_id' => $adjustment->id ?? null,
                    'linked_type' => 'supplier_advance_adjustment',
                    'is_tax_line' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('SupplierFinanceOperation (Adjustment) error: ' . $ex->getMessage());
        }
    }

    /**
     * ===============================
     * REVERSE / DELETE FINANCE
     * ===============================
     */
    public function deleteSupplierFinanceByRef($referenceNo, $voucherType)
    {
        try {
            $finance = DB::table('finance')
                ->where('reference_no', $referenceNo)
                ->where('voucher_type', $voucherType)
                ->first();

            if ($finance) {
                DB::table('finance_sub')->where('finance_id', $finance->id)->delete();
                DB::table('finance')->where('id', $finance->id)->delete();
            }
        } catch (\Exception $ex) {
            Log::error("SupplierFinanceOperation delete failed: " . $ex->getMessage());
        }
    }
}
