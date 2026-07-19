<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\PaymentEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Finance\Payment\PaymentAdditionalTransaction;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\Payment\PaymentInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Models\Supplier\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index()
    {
        return view('modules.transaction.payment.list');
    }

    /**
     * Show the form for creating a new payment.
     */
    public function modal()
    {
        $payment = new Payment();
        $suppliers = Supplier::select('id', 'name_en')->orderBy('name_en')->get();
        //$jobs = Job::select('id', 'row_no')->orderBy('row_no', 'desc')->get();
        //$parentAccounts = Account::whereIn('name', ['Bank Accounts', 'Cash in Hand'])->pluck('id')->toArray();
        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        // Filter for accounts that are intended to be "headers" (optgroups)
        // Usually these are is_grouped = 1

        // Filter for the actual selectable accounts for Paid Through
        $paidThroughAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        // Get all accounts for additional transactions — excluding system-managed
        // equity accounts (Owner Capital, Retained Earnings, Current Year
        // Profit/Loss) that shouldn't be posted to manually.
        $subAccounts = Account::where('is_grouped', 0)->where('type', '!=', 'Equity')->orderBy('name')->get();
        // Initialize empty supplier invoices collection for new payment
        $supplierInvoices = collect();
        $selectedInvoice = [];

        return view('modules.transaction.payment.payment-form', compact('payment', 'suppliers', 'parents', 'paidThroughAccounts', 'subAccounts', 'supplierInvoices', 'selectedInvoice'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit($id)
    {
        $payment = Payment::with(['paymentInvoices', 'additionalTransactions'])->findOrFail($id);

        $suppliers = Supplier::select('id', 'name_en')->orderBy('name_en')->get();

        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        // Filter for the actual selectable accounts for Paid Through
        $paidThroughAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        // Get all accounts for additional transactions — excluding system-managed
        // equity accounts (Owner Capital, Retained Earnings, Current Year
        // Profit/Loss) that shouldn't be posted to manually.
        $subAccounts = Account::where('is_grouped', 0)->where('type', '!=', 'Equity')->orderBy('name')->get(); // All accounts for additional transactions

        // Get the IDs of invoices already included in this payment
        $selectedInvoiceIds = $payment->paymentInvoices->pluck('supplier_invoice_id')->toArray();

        // Get supplier invoices for the selected supplier
        $supplierInvoices = SupplierInvoice::where('supplier_id', $payment->supplier_id)
            ->orderBy('invoice_date', 'desc')
            ->get();

        // For each invoice, calculate the total amount already paid
        foreach ($supplierInvoices as $key => $invoice) {
            // Sum all payment amounts for this invoice, excluding the current payment
            $totalPaid = PaymentInvoice::where('supplier_invoice_id', $invoice->id)
                ->where('payment_id', '!=', $payment->id)
                ->sum('amount');

            // Add the paid amount and remaining balance to the invoice object
            $invoice->paid_amount = $totalPaid;
            $invoice->balance_amount = $invoice->grand_total - $totalPaid;

            // Remove invoices with zero balance unless they are already selected in this payment
            if ($invoice->balance_amount <= 0 && !in_array($invoice->id, $selectedInvoiceIds)) {
                $supplierInvoices->forget($key);
            }
        }

        $selectedInvoice = $payment->paymentInvoices->pluck('amount', 'supplier_invoice_id')->toArray();
        return view('modules.transaction.payment.payment-form', compact('payment', 'suppliers', 'parents', 'paidThroughAccounts', 'subAccounts', 'supplierInvoices', 'selectedInvoice'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['supplier' => decodeId($request->input('supplier'))]);
        $validated = $request->validate([
            'supplier' => 'required|exists:suppliers,id',
            //'job_id' => 'nullable|exists:jobs,id',
            'payment_date' => 'required|date',
            'account' => 'required',
            //'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'currency_rate' => 'required|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'supplier_invoice_ids' => 'required|array',
            'supplier_invoice_ids.*' => 'exists:supplier_invoices,id',
            'invoice_amounts' => 'required|array',
            'invoice_amounts.*' => 'numeric|min:0',
            'additional_transaction_accounts' => 'nullable|array',
            'additional_transaction_accounts.*' => 'exists:accounts,id',
            'additional_transaction_descriptions' => 'nullable|array',
            'additional_transaction_amounts' => 'nullable|array',
            'additional_transaction_amounts.*' => 'numeric|min:0',
            'additional_transaction_types' => 'nullable|array',
            'additional_transaction_types.*' => 'in:debit,credit',
        ]);

        $this->assertPeriodOpen($validated['payment_date'], 'payment_date');

        DB::beginTransaction();
        try {
            // Create or update payment
            $isUpdate = $request->filled('payment_id');
            $oldPaymentInvoices = [];

            if ($isUpdate) {
                $payment = Payment::findOrFail($request->input('payment_id'));
                $payment->updated_by = Auth::id();

                // If this is an approved payment, we need to revert the paid_amount in supplier invoices
                if ($payment->status == PaymentEnum::APPROVED->value) {
                    $oldPaymentInvoices = PaymentInvoice::where('payment_id', $payment->id)
                        ->get()
                        ->keyBy('supplier_invoice_id')
                        ->toArray();

                    // Revert paid_amount in supplier invoices
                    foreach ($oldPaymentInvoices as $invoiceId => $oldPaymentInvoice) {
                        $supplierInvoice = SupplierInvoice::find($invoiceId);
                        if ($supplierInvoice && $supplierInvoice->paid_amount) {
                            $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $oldPaymentInvoice['amount']);
                            $supplierInvoice->save();
                        }
                    }
                }
            } else {
                $payment = new Payment();
                $payment->status = PaymentEnum::DRAFT->value;
                $payment->company_id = companyId();
                $payment->created_by = Auth::id();

                // Generate row_no
                $year = Carbon::today()->format('Y');
                $lastRowNo = Payment::whereYear('payment_date', $year)->max('unique_row_no') ?? 0;
                $payment->unique_row_no = $lastRowNo + 1;
                $payment->row_no = 'PAY/' . date('y') . '/' . sprintf('%04d', $payment->unique_row_no);
            }

            // Set job_no if job_id is provided
            /*if ($validated['job_id']) {
                $job = Job::select('row_no')->find($validated['job_id']);
                $payment->job_no = $job->row_no;
            }*/

            // Calculate totals
            $subTotal = 0;
            $taxTotal = 0;
            $bankCharges = $request->input('bank_charges', 0);
            $otherCharges = $request->input('other_charges', 0);

            foreach ($request->input('supplier_invoice_ids') as $index => $invoiceId) {
                $invoice = SupplierInvoice::find($invoiceId);
                $amount = $request->input('invoice_amounts')[$invoiceId];

                // Validate that amount doesn't exceed the invoice grand_total
                if ($amount > $invoice->grand_total) {
                    return redirect()->back()->withErrors(['error' => 'Payment amount cannot exceed the invoice total amount'])->withInput();
                }

                // Calculate tax proportion
                $invoiceTotal = $invoice->grand_total;
                $taxProportion = 0;

                if ($invoiceTotal > 0) {
                    $taxProportion = ($invoice->tax_total / $invoiceTotal) * $amount;
                }

                $subTotal += $amount - $taxProportion;
                $taxTotal += $taxProportion;
            }

            // Additional transactions each post their own single-sided debit
            // or credit line (see createFinanceEntries below) — their other
            // side comes out of the same Bank/Cash account this payment
            // credits, so grand_total (what's credited to Bank/Cash) must
            // include their net effect or the posting goes out of balance.
            $additionalTransactionsNet = 0;
            if ($request->has('additional_transaction_accounts') && is_array($request->input('additional_transaction_accounts'))) {
                foreach ($request->input('additional_transaction_accounts') as $index => $accountId) {
                    $amount = $request->input('additional_transaction_amounts')[$index] ?? null;
                    if (empty($accountId) || empty($amount)) {
                        continue;
                    }
                    $isDebit = ($request->input('additional_transaction_types')[$index] ?? null) === 'debit';
                    $additionalTransactionsNet += $isDebit ? (float) $amount : -(float) $amount;
                }
            }

            $grandTotal = $subTotal + $taxTotal + $bankCharges + $otherCharges + $additionalTransactionsNet;

            // Update payment fields
            $payment->supplier_id = $validated['supplier'];
            //$payment->job_id = $validated['job_id'];
            $payment->payment_date = $validated['payment_date'];
            $payment->account = $validated['account'][0];
            //$payment->payment_method = $validated['payment_method'];
            $payment->reference_no = $validated['reference_no'] ?? null;
            $payment->currency = $validated['currency'];
            $payment->currency_rate = $validated['currency_rate'];
            $payment->sub_total = $subTotal;
            $payment->tax_total = $taxTotal;
            $payment->bank_charges = $bankCharges;
            $payment->other_charges = $otherCharges;
            $payment->grand_total = $grandTotal;
            $payment->base_sub_total = $subTotal * $validated['currency_rate'];
            $payment->base_tax_total = $taxTotal * $validated['currency_rate'];
            $payment->base_bank_charges = $bankCharges * $validated['currency_rate'];
            $payment->base_other_charges = $otherCharges * $validated['currency_rate'];
            $payment->base_grand_total = $grandTotal * $validated['currency_rate'];
            $payment->notes = $validated['notes'] ?? null;

            $payment->save();

            // Delete existing payment invoices
            PaymentInvoice::where('payment_id', $payment->id)->delete();

            // Delete existing additional transactions
            PaymentAdditionalTransaction::where('payment_id', $payment->id)->delete();

            // Create payment invoices
            $paymentInvoices = [];
            foreach ($request->input('supplier_invoice_ids') as $index => $invoiceId) {
                $paymentInvoices[] = [
                    'payment_id' => $payment->id,
                    'supplier_invoice_id' => $invoiceId,
                    'company_id' => companyId(),
                    'amount' => $request->input('invoice_amounts')[$invoiceId],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Create additional transactions if any
            $additionalTransactions = [];
            if ($request->has('additional_transaction_accounts') && is_array($request->input('additional_transaction_accounts'))) {
                foreach ($request->input('additional_transaction_accounts') as $index => $accountId) {
                    if (empty($accountId) || empty($request->input('additional_transaction_amounts')[$index])) {
                        continue;
                    }

                    $additionalTransactions[] = [
                        'payment_id' => $payment->id,
                        'account_id' => $accountId,
                        'description' => $request->input('additional_transaction_descriptions')[$index] ?? '',
                        'amount' => $request->input('additional_transaction_amounts')[$index],
                        'is_debit' => $request->input('additional_transaction_types')[$index] === 'debit',
                        'company_id' => companyId(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($paymentInvoices)) {
                DB::table('payment_invoices')->insert($paymentInvoices);

                // If this is an approved payment, update the paid_amount in supplier invoices
                if ($payment->status == PaymentEnum::APPROVED->value) {
                    foreach ($request->input('supplier_invoice_ids') as $index => $invoiceId) {
                        $amount = $request->input('invoice_amounts')[$invoiceId];
                        $supplierInvoice = SupplierInvoice::find($invoiceId);
                        if ($supplierInvoice) {
                            $supplierInvoice->paid_amount = ($supplierInvoice->paid_amount ?? 0) + $amount;
                            $supplierInvoice->save();
                        }
                    }
                }
            }

            // Insert additional transactions if any
            if (!empty($additionalTransactions)) {
                DB::table('payment_additional_transactions')->insert($additionalTransactions);
            }

            // If payment is approved, update finance entries
            if ($payment->status == PaymentEnum::APPROVED->value) {
                $this->createFinanceEntries($payment);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment saved successfully',
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.payment.show', compact('payment'));
    }

    /**
     * Return the drawer (offcanvas) partial for the specified payment.
     */
    public function overviewDrawer($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'additionalTransactions.account', 'documents', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.payment.view-overview-drawer', compact('payment'));
    }

    /**
     * Get supplier invoices for a specific supplier.
     */
    public function getSupplierInvoices($supplierId)
    {
        $invoices = [];
        if ($supplierId) {
            // Get all approved invoices for the supplier
            $invoices = SupplierInvoice::where('supplier_id', decodeId($supplierId))
                ->where('status', 3) // Assuming 3 is the status for approved invoices
                ->orderBy('invoice_date', 'desc')
                ->get();

            // For each invoice, calculate the total amount already paid
            foreach ($invoices as $key => $invoice) {
                // Sum all payment amounts for this invoice
                $totalPaid = PaymentInvoice::where('supplier_invoice_id', $invoice->id)->sum('amount');

                // Add the paid amount and remaining balance to the invoice object
                $invoice->paid_amount = $totalPaid;
                $invoice->balance_amount = $invoice->grand_total - $totalPaid;

                // Remove invoices with zero balance
                if ($invoice->balance_amount <= 0) {
                    $invoices->forget($key);
                }
            }
        }

        return response()->json($invoices->values());
    }

    /**
     * Update the payment status.
     */
    public function updateStatus($id, $status)
    {
        $payment = Payment::findOrFail($id);
        $previousStatus = $payment->status;

        DB::beginTransaction();
        try {
            if ($status == PaymentEnum::APPROVED->value) {
                $payment->status = PaymentEnum::APPROVED->value;
                $payment->approved_by = Auth::id();
                $payment->approved_at = now();

                // Update paid_amount in supplier invoices when payment is approved
                if ($previousStatus != PaymentEnum::APPROVED->value) {
                    $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
                    foreach ($paymentInvoices as $paymentInvoice) {
                        $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                        if ($supplierInvoice) {
                            $supplierInvoice->paid_amount = ($supplierInvoice->paid_amount ?? 0) + $paymentInvoice->amount;
                            $supplierInvoice->save();
                        }
                    }
                }
            } elseif ($status == PaymentEnum::CANCELLED->value) {
                $payment->status = PaymentEnum::CANCELLED->value;
            } else {
                $payment->status = PaymentEnum::DRAFT->value;

                // Revert paid_amount in supplier invoices when payment is moved back to draft
                if ($previousStatus == PaymentEnum::APPROVED->value) {
                    $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
                    foreach ($paymentInvoices as $paymentInvoice) {
                        $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                        if ($supplierInvoice && $supplierInvoice->paid_amount) {
                            $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $paymentInvoice->amount);
                            $supplierInvoice->save();
                        }
                    }
                }
            }

            $payment->save();

            // Create finance entries when payment is approved
            if ($status == PaymentEnum::APPROVED->value) {
                $this->createFinanceEntries($payment);
            }

            // Delete finance entries when payment is moved back to draft
            if ($previousStatus == PaymentEnum::APPROVED->value && $status == PaymentEnum::DRAFT->value) {
                $this->deleteFinanceEntries($payment->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated successfully',
                'data' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating payment status: ' . $e->getMessage(),
            ], 500);
        } finally {
            DB::commit();
        }


    }

    /**
     * Set disapproval reason for a payment.
     */
    public function setDisapprovalReason(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $payment = Payment::findOrFail($id);
        $previousStatus = $payment->status;
        $payment->status = PaymentEnum::CANCELLED->value;
        $payment->disapproval_reason = $validated['reason'];
        $payment->save();

        // Delete finance entries if payment was previously approved
        if ($previousStatus == PaymentEnum::APPROVED->value) {
            // Revert paid_amount in supplier invoices
            $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
            foreach ($paymentInvoices as $paymentInvoice) {
                $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                if ($supplierInvoice && $supplierInvoice->paid_amount) {
                    $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $paymentInvoice->amount);
                    $supplierInvoice->save();
                }
            }

            $this->deleteFinanceEntries($payment->id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment disapproved successfully',
            'data' => [
                'id' => $payment->id,
                'status' => $payment->status,
            ],
        ]);
    }

    /**
     * Print the specified payment.
     */
    public function print($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.payment.print', compact('payment'));
    }

    /**
     * Download the specified payment as PDF.
     */
    public function download($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        $html = view('modules.transaction.payment.print', compact('payment'))->render();
        $fileName = "Payment_{$payment->row_no}.pdf";

        return createPDF($html, $fileName);
    }

    /**
     * Get data for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $query = Payment::with(['supplier:id,name_en,name_ar', 'job:id,row_no'])
            ->select([
                'payments.id',
                'row_no',
                'supplier_id',
                //'job_id',
                //'job_no',
                'payment_date',
                //'payment_method',
                'account',
                'reference_no',
                'currency',
                'grand_total',
                'status',
                'company_id',
                'payments.created_at',
            ])
            ->when($request->tab, function ($q) use ($request) {
                if ($request->tab !== 'all') {
                    $q->where('status', PaymentEnum::fromName($request->tab));
                }
            })
            ->orderBy('payments.id', 'desc');

        // Get counts per status
        $statusCounts = Payment::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses
        $allCounts = [];
        foreach (PaymentEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Payment #' . htmlspecialchars($model->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'payment-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('payment_date', fn($model) => \Carbon\Carbon::parse($model->payment_date)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('supplier_name', fn($model) => $model->supplier->name_en ?? '-')
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, decimals()))
            ->editColumn('status', fn($model) => PaymentEnum::from($model->status)->label())
            ->with([
                'statusCounts' => $allCounts,
            ])
            ->toJson();
    }

    /**
     * Get context menu actions for a payment.
     */
    public function actions($id)
    {
        $payment = Payment::select('id', 'row_no', 'status')->findOrFail($id);
        $contextMenu = collect([]);

        // Status actions
        if ($payment->status === PaymentEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approved'),
                        'id' => 'row_approved',
                        'data-id' => $payment->id,
                        'data-value' => PaymentEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'id' => 'row_cancelled',
                        'class' => 'row_cancelled',
                        'data-id' => $payment->id,
                        'data-value' => PaymentEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($payment->status === PaymentEnum::APPROVED->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Move To Draft'),
                        'id' => 'row_draft',
                        'data-id' => $payment->id,
                        'data-value' => PaymentEnum::DRAFT->value,
                        'icon' => 'pending'
                    ]
                ]
            ]);
        }

        // View, Print, Download actions
        $contextMenu->push([
            'label' => __('View'),
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $payment->id,
            'type' => 'item',
            'icon' => 'view',
        ]);

        $contextMenu->push([
            'label' => __('Print'),
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $payment->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'PAYMENT.printPreview(' . $payment->id . ')',
        ]);

        $contextMenu->push([
            'label' => __('Download'),
            'id' => 'row_download',
            'class' => 'row_download',
            'data-id' => $payment->id,
            'type' => 'item',
            'icon' => 'download',
            'onclick' => 'PAYMENT.downloadPDF(' . $payment->id . ')',
            'separator' => 'after',
        ]);

        // Edit and Delete actions (only for Draft status)
        if ($payment->status === PaymentEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $payment->id,
                'type' => 'item',
                'icon' => 'edit'
            ];

            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit]
            ]);
        }

        return response()->json($contextMenu->values());
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== PaymentEnum::DRAFT->value) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft payments can be deleted',
            ], 400);
        }

        // If this is an approved payment, revert the paid_amount in supplier invoices
        if ($payment->status == PaymentEnum::APPROVED->value) {
            $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
            foreach ($paymentInvoices as $paymentInvoice) {
                $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                if ($supplierInvoice && $supplierInvoice->paid_amount) {
                    $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $paymentInvoice->amount);
                    $supplierInvoice->save();
                }
            }
        }

        // Delete any associated finance entries
        $this->deleteFinanceEntries($payment->id);

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully',
        ]);
    }

    /**
     * Create finance entries for a payment.
     *
     * @param Payment $payment
     * @return void
     */
    /**
     * Create finance entries for a payment.
     *
     * Correct accounting for supplier payment:
     *   DR  Accounts Payable (AP)   (full invoice amount settled, incl. VAT — AP was credited in full on invoice)
     *   DR  Bank Charges Expense    (if any)
     *   DR  Miscellaneous Expenses  (other charges, if any)
     *   CR  Bank/Cash account       (grand_total = invoices paid + bank_charges + other_charges)
     *
     * NOTE: Input VAT is NOT touched here — it was already claimed as a Debit on the supplier invoice.
     *       The AP balance already includes VAT, so we simply DR AP for the full amount paid.
     *
     * @param Payment $payment
     * @return void
     */
    private function createFinanceEntries(Payment $payment)
    {
        // Delete any existing finance entries for this payment
        $this->deleteFinanceEntries($payment->id);

        // Create a new finance entry
        $finance = new Finance();
        $finance->voucher_no = $payment->row_no;
        $finance->voucher_type = 'PV'; // Payment Voucher
        $finance->reference_no = $payment->reference_no;
        $finance->reference_date = $payment->payment_date;
        $finance->supplier_id = $payment->supplier_id;
        $finance->narration = $payment->notes ?? 'Payment to supplier';
        $finance->currency = $payment->currency;
        $finance->exchange_rate = $payment->currency_rate;
        $finance->total_debit = $payment->grand_total;
        $finance->total_credit = $payment->grand_total;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $payment->base_grand_total;
        $finance->base_total_credit = $payment->base_grand_total;
        $finance->job_id = $payment->job_id;
        $finance->job_no = $payment->job_no ?? '';
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $payment->id;
        $finance->linked_type = Payment::class;
        $finance->company_id = $payment->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        // Get payment invoices
        $paymentInvoices = PaymentInvoice::with('supplierInvoice')
            ->where('payment_id', $payment->id)
            ->get();

        $financeSubs = [];

        $commonData = [
            'finance_id'        => $finance->id,
            'voucher_no'        => $finance->voucher_no,
            'voucher_type'      => $finance->voucher_type,
            'reference_no'      => $finance->reference_no,
            'supplier_id'       => $payment->supplier_id,
            'base_currency'     => 'SAR',
            'exchange_rate'     => $payment->currency_rate,
            'currency'          => $payment->currency,
            'job_id'            => $payment->job_id ?? null,
            'job_no'            => $payment->job_no ?? '',
            'cost_center_id'    => null,
            'is_tax_line'       => 0,
            'is_auto_generated' => 1,
            'linked_id'         => $payment->id,
            'linked_type'       => Payment::class,
            'user_id'           => Auth::id(),
            'company_id'        => $payment->company_id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        // CR Bank/Cash account (full grand_total goes out — invoices + charges)
        $financeSubs[] = array_merge($commonData, [
            'account_id'     => $payment->account,
            'reference_date' => formDate($payment->payment_date),
            'description'    => 'Payment to supplier',
            'debit'          => 0,
            'credit'         => $payment->grand_total,
            'base_debit'     => 0,
            'base_credit'    => $payment->base_grand_total,
        ]);

        // DR Accounts Payable (full invoice amounts being settled, including VAT)
        foreach ($paymentInvoices as $paymentInvoice) {
            $invoice = $paymentInvoice->supplierInvoice;

            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 18, // 2110 Accounts Payable
                'reference_date' => formDate($payment->payment_date),
                'reference_no'   => $invoice->row_no,
                'description'    => 'Payment for invoice ' . $invoice->row_no,
                'debit'          => $paymentInvoice->amount,
                'credit'         => 0,
                'base_debit'     => $paymentInvoice->amount * $payment->currency_rate,
                'base_credit'    => 0,
                'job_id'         => $invoice->job_id ?? null,
                'job_no'         => $invoice->job_no ?? '',
                'linked_id'      => $paymentInvoice->id,
                'linked_type'    => PaymentInvoice::class,
            ]);
        }

        // DR Bank Charges Expense (if any)
        if (($payment->bank_charges ?? 0) > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 54, // 5260 Bank Charges expense account
                'reference_date' => formDate($payment->payment_date),
                'description'    => 'Bank charges on payment',
                'debit'          => $payment->bank_charges,
                'credit'         => 0,
                'base_debit'     => $payment->base_bank_charges,
                'base_credit'    => 0,
            ]);
        }

        // DR Miscellaneous Expenses (other charges, if any)
        if (($payment->other_charges ?? 0) > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 56, // 5280 Miscellaneous Expenses account
                'reference_date' => formDate($payment->payment_date),
                'description'    => 'Other charges on payment',
                'debit'          => $payment->other_charges,
                'credit'         => 0,
                'base_debit'     => $payment->base_other_charges,
                'base_credit'    => 0,
            ]);
        }

        // Add entries for additional transactions if any
        $additionalTransactions = PaymentAdditionalTransaction::where('payment_id', $payment->id)->get();
        foreach ($additionalTransactions as $transaction) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => $transaction->account_id,
                'reference_date' => formDate($payment->payment_date),
                'description'    => $transaction->description ?: 'Additional transaction',
                'debit'          => $transaction->is_debit ? $transaction->amount : 0,
                'credit'         => !$transaction->is_debit ? $transaction->amount : 0,
                'base_debit'     => $transaction->is_debit ? $transaction->amount * $payment->currency_rate : 0,
                'base_credit'    => !$transaction->is_debit ? $transaction->amount * $payment->currency_rate : 0,
                'linked_id'      => $transaction->id,
                'linked_type'    => PaymentAdditionalTransaction::class,
            ]);
        }

        // Insert all finance sub entries
        FinanceSub::insert($financeSubs);
    }

    /**
     * Delete finance entries for a payment.
     *
     * @param int $paymentId
     * @return void
     */
    private function deleteFinanceEntries($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // Get finance entries using the relationship and delete them
        // This will also delete related finance sub entries due to the cascade delete in the database
        foreach ($payment->financeEntries as $finance) {
            // Delete finance sub entries first
            $finance->financeSubs()->delete();

            // Then delete the finance entry
            $finance->delete();
        }
    }
}
