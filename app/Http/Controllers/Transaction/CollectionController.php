<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\CollectionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\Collection\CollectionInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Job\Job;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CollectionController extends Controller
{
    /**
     * Display a listing of the collections.
     */
    public function index()
    {
        return view('modules.transaction.collection.list');
    }

    /**
     * Show the form for creating a new collection.
     */
    public function modal()
    {
        $collection = new Collection();
        $customers = Customer::select('id', 'name_en')->orderBy('name_en')->get();
        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();
        // Initialize empty supplier invoices collection for new payment
        $customerInvoices = collect();
        $selectedInvoice = [];

        return view('modules.transaction.collection.collection-form', compact('collection', 'customers', 'parents', 'subAccounts', 'customerInvoices', 'selectedInvoice'));
    }

    /**
     * Show the form for editing the specified collection.
     */
    public function edit($id)
    {
        $collection = Collection::with('collectionInvoices')->findOrFail($id);
        $customers = Customer::select('id', 'name_en')->orderBy('name_en')->get();

        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        // Get the IDs of invoices already included in this collection
        $selectedInvoiceIds = $collection->collectionInvoices->pluck('customer_invoice_id')->toArray();

        // Get customer invoices for the selected customer
        $customerInvoices = CustomerInvoice::where('customer_id', $collection->customer_id)
            ->orderBy('invoice_date', 'desc')
            ->get();

        // For each invoice, calculate the total amount already paid
        foreach ($customerInvoices as $key => $invoice) {
            // Sum all collection amounts for this invoice, excluding the current collection
            $totalPaid = CollectionInvoice::where('customer_invoice_id', $invoice->id)
                ->where('collection_id', '!=', $collection->id)
                ->sum('amount');

            // Add the paid amount and remaining balance to the invoice object
            $invoice->paid_amount = $totalPaid;
            $invoice->balance_amount = $invoice->grand_total - $totalPaid;

            // Remove invoices with zero balance unless they are already selected in this collection
            if ($invoice->balance_amount <= 0 && !in_array($invoice->id, $selectedInvoiceIds)) {
                $customerInvoices->forget($key);
            }
        }

        $selectedInvoice = $collection->collectionInvoices->pluck('amount', 'customer_invoice_id')->toArray();
        //$customerInvoices = $customerInvoices->values();
        return view('modules.transaction.collection.collection-form', compact('collection', 'customers', 'parents', 'subAccounts', 'customerInvoices', 'selectedInvoice'));
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $validated = $request->validate([
            'customer' => 'required|exists:customers,id',
            //'job_id' => 'nullable|exists:jobs,id',
            'collection_date' => 'required|date',
            'account' => 'required',
            //'collection_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'currency_rate' => 'required|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'customer_invoice_ids' => 'required|array',
            'customer_invoice_ids.*' => 'exists:customer_invoices,id',
            'invoice_amounts' => 'required|array',
            'invoice_amounts.*' => 'numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create or update collection
            $isUpdate = $request->filled('collection_id');
            $oldCollectionInvoices = [];

            if ($isUpdate) {
                $collection = Collection::findOrFail($request->input('collection_id'));
                $collection->updated_by = Auth::id();

                // If this is an approved collection, we need to revert the paid_amount in customer invoices
                if ($collection->status == CollectionEnum::APPROVED->value) {
                    $oldCollectionInvoices = CollectionInvoice::where('collection_id', $collection->id)
                        ->get()
                        ->keyBy('customer_invoice_id')
                        ->toArray();

                    // Revert paid_amount in customer invoices
                    foreach ($oldCollectionInvoices as $invoiceId => $oldCollectionInvoice) {
                        $customerInvoice = CustomerInvoice::find($invoiceId);
                        if ($customerInvoice && $customerInvoice->paid_amount) {
                            $customerInvoice->paid_amount = max(0, $customerInvoice->paid_amount - $oldCollectionInvoice['amount']);
                            $customerInvoice->save();
                        }
                    }
                }
            } else {
                $collection = new Collection();
                $collection->status = CollectionEnum::DRAFT->value;
                $collection->company_id = companyId();
                $collection->created_by = Auth::id();

                // Generate row_no
                $year = Carbon::today()->format('Y');
                $lastRowNo = Collection::whereYear('collection_date', $year)->max('unique_row_no') ?? 0;
                $collection->unique_row_no = $lastRowNo + 1;
                $collection->row_no = 'COL/' . date('y') . '/' . sprintf('%04d', $collection->unique_row_no);
            }

            // Set job_no if job_id is provided
            /*if ($validated['job_id']) {
                $job = Job::select('row_no')->find($validated['job_id']);
                $collection->job_no = $job->row_no;
            }*/

            // Calculate totals
            $subTotal = 0;
            $taxTotal = 0;
            $bankCharges = $request->input('bank_charges', 0);
            $otherCharges = $request->input('other_charges', 0);

            foreach ($request->input('customer_invoice_ids') as $index => $invoiceId) {
                $invoice = CustomerInvoice::find($invoiceId);
                $amount = $request->input('invoice_amounts')[$invoiceId];

                // Validate that amount doesn't exceed the invoice grand_total
                if ($amount > $invoice->grand_total) {
                    return redirect()->back()->withErrors(['error' => 'Collection amount cannot exceed the invoice total amount'])->withInput();
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

            $grandTotal = $subTotal + $taxTotal + $bankCharges + $otherCharges;

            // Update collection fields
            $collection->customer_id = $validated['customer'];
            //$collection->job_id = $validated['job_id'];
            $collection->collection_date = $validated['collection_date'];
            $collection->account = $validated['account'][0];
            //$collection->collection_method = $validated['collection_method'];
            $collection->reference_no = $validated['reference_no'];
            $collection->currency = $validated['currency'];
            $collection->currency_rate = $validated['currency_rate'];
            $collection->sub_total = $subTotal;
            $collection->tax_total = $taxTotal;
            $collection->bank_charges = $bankCharges;
            $collection->other_charges = $otherCharges;
            $collection->grand_total = $grandTotal;
            $collection->base_sub_total = $subTotal * $validated['currency_rate'];
            $collection->base_tax_total = $taxTotal * $validated['currency_rate'];
            $collection->base_bank_charges = $bankCharges * $validated['currency_rate'];
            $collection->base_other_charges = $otherCharges * $validated['currency_rate'];
            $collection->base_grand_total = $grandTotal * $validated['currency_rate'];
            $collection->notes = $validated['notes'];

            $collection->save();

            // Delete existing collection invoices
            CollectionInvoice::where('collection_id', $collection->id)->delete();

            // Create collection invoices
            $collectionInvoices = [];
            foreach ($request->input('customer_invoice_ids') as $index => $invoiceId) {
                $collectionInvoices[] = [
                    'collection_id' => $collection->id,
                    'customer_invoice_id' => $invoiceId,
                    'company_id' => companyId(),
                    'amount' => $request->input('invoice_amounts')[$invoiceId],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($collectionInvoices)) {
                DB::table('collection_invoices')->insert($collectionInvoices);

                // If this is an approved collection, update the paid_amount in customer invoices
                if ($collection->status == CollectionEnum::APPROVED->value) {
                    foreach ($request->input('customer_invoice_ids') as $index => $invoiceId) {
                        $amount = $request->input('invoice_amounts')[$invoiceId];
                        $customerInvoice = CustomerInvoice::find($invoiceId);
                        if ($customerInvoice) {
                            $customerInvoice->paid_amount = ($customerInvoice->paid_amount ?? 0) + $amount;
                            $customerInvoice->save();
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Collection saved successfully',
                'collection_id' => $collection->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving collection: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified collection.
     */
    public function show($id)
    {
        $collection = Collection::with(['customer', 'job', 'collectionInvoices.customerInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.collection.show', compact('collection'));
    }

    /**
     * Get customer invoices for a specific customer.
     */
    public function getCustomerInvoices($customerId)
    {
        $invoices = [];
        if ($customerId) {
            // Get all approved invoices for the customer
            $invoices = CustomerInvoice::where('customer_id', decodeId($customerId))
                ->where('status', 3) // Assuming 3 is the status for approved invoices
                ->orderBy('invoice_date', 'desc')
                ->get();

            // For each invoice, calculate the total amount already paid
            foreach ($invoices as $key => $invoice) {
                // Sum all collection amounts for this invoice
                $totalPaid = CollectionInvoice::where('customer_invoice_id', $invoice->id)->sum('amount');

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
     * Update the collection status.
     */
    public function updateStatus($id, $status)
    {
        $collection = Collection::findOrFail($id);
        $previousStatus = $collection->status;

        DB::beginTransaction();
        try {
            if ($status == CollectionEnum::APPROVED->value) {
                $collection->status = CollectionEnum::APPROVED->value;
                $collection->approved_by = Auth::id();
                $collection->approved_at = now();

                // Update paid_amount in customer invoices when collection is approved
                if ($previousStatus != CollectionEnum::APPROVED->value) {
                    $collectionInvoices = CollectionInvoice::where('collection_id', $collection->id)->get();
                    foreach ($collectionInvoices as $collectionInvoice) {
                        $customerInvoice = CustomerInvoice::find($collectionInvoice->customer_invoice_id);
                        if ($customerInvoice) {
                            $customerInvoice->paid_amount = ($customerInvoice->paid_amount ?? 0) + $collectionInvoice->amount;
                            $customerInvoice->save();
                        }
                    }
                }
            } elseif ($status == CollectionEnum::CANCELLED->value) {
                $collection->status = CollectionEnum::CANCELLED->value;
            } else {
                $collection->status = CollectionEnum::DRAFT->value;

                // Revert paid_amount in customer invoices when collection is moved back to draft
                if ($previousStatus == CollectionEnum::APPROVED->value) {
                    $collectionInvoices = CollectionInvoice::where('collection_id', $collection->id)->get();
                    foreach ($collectionInvoices as $collectionInvoice) {
                        $customerInvoice = CustomerInvoice::find($collectionInvoice->customer_invoice_id);
                        if ($customerInvoice && $customerInvoice->paid_amount) {
                            $customerInvoice->paid_amount = max(0, $customerInvoice->paid_amount - $collectionInvoice->amount);
                            $customerInvoice->save();
                        }
                    }
                }
            }

            $collection->save();

            // Create finance entries when collection is approved
            if ($status == CollectionEnum::APPROVED->value) {
                $this->createFinanceEntries($collection);
            }

            // Delete finance entries when collection is moved back to draft
            if ($previousStatus == CollectionEnum::APPROVED->value && $status == CollectionEnum::DRAFT->value) {
                $this->deleteFinanceEntries($collection->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Collection status updated successfully',
                'data' => [
                    'id' => $collection->id,
                    'status' => $collection->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating collection status: ' . $e->getMessage(),
            ], 500);
        } finally {
            DB::commit();
        }
    }

    /**
     * Set disapproval reason for a collection.
     */
    public function setDisapprovalReason(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $collection = Collection::findOrFail($id);
        $previousStatus = $collection->status;
        $collection->status = CollectionEnum::CANCELLED->value;
        $collection->disapproval_reason = $validated['reason'];
        $collection->save();

        // Delete finance entries if collection was previously approved
        if ($previousStatus == CollectionEnum::APPROVED->value) {
            // Revert paid_amount in customer invoices
            $collectionInvoices = CollectionInvoice::where('collection_id', $collection->id)->get();
            foreach ($collectionInvoices as $collectionInvoice) {
                $customerInvoice = CustomerInvoice::find($collectionInvoice->customer_invoice_id);
                if ($customerInvoice && $customerInvoice->paid_amount) {
                    $customerInvoice->paid_amount = max(0, $customerInvoice->paid_amount - $collectionInvoice->amount);
                    $customerInvoice->save();
                }
            }

            $this->deleteFinanceEntries($collection->id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Collection cancelled successfully',
            'data' => [
                'id' => $collection->id,
                'status' => $collection->status,
            ],
        ]);
    }

    /**
     * Print the specified collection.
     */
    public function print($id)
    {
        $collection = Collection::with(['customer', 'job', 'collectionInvoices.customerInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.collection.print', compact('collection'));
    }

    /**
     * Download the specified collection as PDF.
     */
    public function download($id)
    {
        $collection = Collection::with(['customer', 'job', 'collectionInvoices.customerInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        $html = view('modules.transaction.collection.print', compact('collection'))->render();
        $fileName = "Collection_{$collection->row_no}.pdf";

        return createPDF($html, $fileName);
    }

    /**
     * Get data for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $query = Collection::with(['customer:id,name_en,name_ar', 'job:id,row_no'])
            ->select([
                'collections.id',
                'row_no',
                'customer_id',
                'account',
                //'job_id',
                //'job_no',
                'collection_date',
                //'collection_method',
                'reference_no',
                'currency',
                'grand_total',
                'status',
                'company_id',
                'collections.created_at',
            ])
            ->when($request->tab, function ($q) use ($request) {
                if ($request->tab !== 'all') {
                    $q->where('status', CollectionEnum::fromName($request->tab));
                }
            })
            ->orderBy('collections.id', 'desc');

        // Get counts per status
        $statusCounts = Collection::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses
        $allCounts = [];
        foreach (CollectionEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Collection #' . htmlspecialchars($model->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'collection-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('collection_date', fn($model) => \Carbon\Carbon::parse($model->collection_date)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('customer_name', fn($model) => $model->customer->name_en ?? '-')
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, decimals()))
            ->editColumn('status', fn($model) => CollectionEnum::from($model->status)->label())
            ->with([
                'statusCounts' => $allCounts,
            ])
            ->toJson();
    }

    /**
     * Get context menu actions for a collection.
     */
    public function actions($id)
    {
        $collection = Collection::select('id', 'row_no', 'status')->findOrFail($id);
        $contextMenu = collect([]);

        // Status actions
        if ($collection->status === CollectionEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approved'),
                        'id' => 'row_approved',
                        'data-id' => $collection->id,
                        'data-value' => CollectionEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'id' => 'row_cancelled',
                        'class' => 'row_cancelled',
                        'data-id' => $collection->id,
                        'data-value' => CollectionEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($collection->status === CollectionEnum::APPROVED->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Move To Draft'),
                        'id' => 'row_draft',
                        'data-id' => $collection->id,
                        'data-value' => CollectionEnum::DRAFT->value,
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
            'data-id' => $collection->id,
            'type' => 'item',
            'icon' => 'view',
        ]);

        $contextMenu->push([
            'label' => __('Print'),
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $collection->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'COLLECTION.printPreview(' . $collection->id . ')',
        ]);

        $contextMenu->push([
            'label' => __('Download'),
            'id' => 'row_download',
            'class' => 'row_download',
            'data-id' => $collection->id,
            'type' => 'item',
            'icon' => 'download',
            'onclick' => 'COLLECTION.downloadPDF(' . $collection->id . ')',
            'separator' => 'after',
        ]);

        // Edit and Delete actions (only for Draft status)
        if ($collection->status === CollectionEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $collection->id,
                'type' => 'item',
                'icon' => 'edit'
            ];

            /*$delete = [
                'label' => __('Delete'),
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $collection->id,
                'type' => 'item',
                'icon' => 'delete'
            ];*/

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
     * Remove the specified collection from storage.
     */
    public function destroy($id)
    {
        $collection = Collection::findOrFail($id);

        if ($collection->status !== CollectionEnum::DRAFT->value) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft collections can be deleted',
            ], 400);
        }

        $collection->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Collection deleted successfully',
        ]);
    }

    /**
     * Create finance entries for a collection.
     *
     * @param Collection $collection
     * @return void
     */
    private function createFinanceEntries(Collection $collection)
    {
        // Delete any existing finance entries for this collection
        $this->deleteFinanceEntries($collection->id);

        // Create a new finance entry
        $finance = new Finance();
        $finance->voucher_no = $collection->row_no;
        $finance->voucher_type = 'CV'; // Collection Voucher
        $finance->reference_no = $collection->reference_no;
        $finance->reference_date = $collection->collection_date;
        $finance->customer_id = $collection->customer_id;
        $finance->narration = $collection->notes ?? 'Collection from customer';
        $finance->currency = $collection->currency;
        $finance->exchange_rate = $collection->currency_rate;
        $finance->total_debit = $collection->grand_total;
        $finance->total_credit = $collection->grand_total;
        $finance->base_currency = 'SAR'; // Assuming SAR is the base currency
        $finance->base_total_debit = $collection->base_grand_total;
        $finance->base_total_credit = $collection->base_grand_total;
        $finance->job_id = $collection->job_id ?? 0;
        $finance->job_no = $collection->job_no ?? '';
        $finance->is_approved = 1; // Approved
        $finance->posted_at = now();
        $finance->linked_id = $collection->id;
        $finance->linked_type = Collection::class;
        $finance->company_id = $collection->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        // Get collection invoices
        $collectionInvoices = CollectionInvoice::with('customerInvoice')
            ->where('collection_id', $collection->id)
            ->get();

        // Create finance sub entries
        $financeSubs = [];

        // Debit entry for the bank/cash account
        $financeSubs[] = [
            'finance_id' => $finance->id,
            'voucher_no' => $finance->voucher_no,
            'voucher_type' => $finance->voucher_type,
            'reference_no' => $finance->reference_no,
            'customer_id' => $collection->customer_id,
            'account_id' => $collection->account, // Bank/Cash account
            'reference_date' => formDate($collection->collection_date),
            'description' => 'Collection from customer',
            'debit' => $collection->grand_total,
            'credit' => 0,
            'currency' => $collection->currency,
            'base_debit' => $collection->base_grand_total,
            'base_credit' => 0,
            'base_currency' => 'SAR',
            'exchange_rate' => $collection->currency_rate,
            'job_id' => $collection->job_id ?? null,
            'job_no' => $collection->job_no ?? '',
            'cost_center_id' => null,
            'is_tax_line' => 0,
            'is_auto_generated' => 1,
            'linked_id' => $collection->id,
            'linked_type' => Collection::class,
            'user_id' => Auth::id(),
            'company_id' => $collection->company_id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Credit entries for each invoice
        foreach ($collectionInvoices as $collectionInvoice) {
            $invoice = $collectionInvoice->customerInvoice;

            // Calculate tax proportion
            $invoiceTotal = $invoice->grand_total;
            $taxProportion = 0;

            if ($invoiceTotal > 0) {
                $taxProportion = ($invoice->tax_total / $invoiceTotal) * $collectionInvoice->amount;
            }

            $subTotal = $collectionInvoice->amount - $taxProportion;

            // Credit entry for the customer account (AR account)
            $financeSubs[] = [
                'finance_id' => $finance->id,
                'voucher_no' => $finance->voucher_no,
                'voucher_type' => $finance->voucher_type,
                'reference_no' => $invoice->row_no,
                'customer_id' => $collection->customer_id,
                'account_id' => 5, // Using 1130 as the Accounts Receivable account
                'reference_date' => formDate($collection->collection_date),
                'description' => 'Collection for invoice ' . $invoice->row_no,
                'debit' => 0,
                'credit' => $subTotal,
                'currency' => $collection->currency,
                'base_debit' => 0,
                'base_credit' => $subTotal * $collection->currency_rate,
                'base_currency' => 'SAR',
                'exchange_rate' => $collection->currency_rate,
                'job_id' => $invoice->job_id ?? null,
                'job_no' => $invoice->job_no ?? '',
                'cost_center_id' => null,
                'is_tax_line' => 0,
                'is_auto_generated' => 1,
                'linked_id' => $collectionInvoice->id,
                'linked_type' => CollectionInvoice::class,
                'user_id' => Auth::id(),
                'company_id' => $collection->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // If there's tax, add a tax entry
            if ($taxProportion > 0) {
                $financeSubs[] = [
                    'finance_id' => $finance->id,
                    'voucher_no' => $finance->voucher_no,
                    'voucher_type' => $finance->voucher_type,
                    'reference_no' => $invoice->row_no,
                    'customer_id' => $collection->customer_id,
                    'account_id' => 20, // Assuming 2130 is the VAT account
                    'reference_date' => formDate($collection->collection_date),
                    'description' => 'VAT for invoice ' . $invoice->row_no,
                    'debit' => 0,
                    'credit' => $taxProportion,
                    'currency' => $collection->currency,
                    'base_debit' => 0,
                    'base_credit' => $taxProportion * $collection->currency_rate,
                    'base_currency' => 'SAR',
                    'exchange_rate' => $collection->currency_rate,
                    'job_id' => $invoice->job_id ?? null,
                    'job_no' => $invoice->job_no ?? '',
                    'cost_center_id' => null,
                    'is_tax_line' => 1,
                    'is_auto_generated' => 1,
                    'linked_id' => $collectionInvoice->id,
                    'linked_type' => CollectionInvoice::class,
                    'user_id' => Auth::id(),
                    'company_id' => $collection->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Add bank charges if any
        if ($collection->bank_charges > 0) {
            $financeSubs[] = [
                'finance_id' => $finance->id,
                'voucher_no' => $finance->voucher_no,
                'voucher_type' => $finance->voucher_type,
                'reference_no' => $finance->reference_no,
                'customer_id' => $collection->customer_id,
                'account_id' => 54, // Assuming 5260 is the Bank Charges account
                'reference_date' => formDate($collection->collection_date),
                'description' => 'Bank charges',
                'debit' => 0,
                'credit' => $collection->bank_charges,
                'currency' => $collection->currency,
                'base_debit' => 0,
                'base_credit' => $collection->base_bank_charges,
                'base_currency' => 'SAR',
                'exchange_rate' => $collection->currency_rate,
                'job_id' => $collection->job_id ?? null,
                'job_no' => $collection->job_no ?? '',
                'cost_center_id' => null,
                'is_tax_line' => 0,
                'is_auto_generated' => 1,
                'linked_id' => $collection->id,
                'linked_type' => Collection::class,
                'user_id' => Auth::id(),
                'company_id' => $collection->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Add other charges if any
        if ($collection->other_charges > 0) {
            $financeSubs[] = [
                'finance_id' => $finance->id,
                'voucher_no' => $finance->voucher_no,
                'voucher_type' => $finance->voucher_type,
                'reference_no' => $finance->reference_no,
                'customer_id' => $collection->customer_id,
                'account_id' => 56, // Assuming 5280 is the Other Charges account
                'reference_date' => formDate($collection->collection_date),
                'description' => 'Other charges',
                'debit' => 0,
                'credit' => $collection->other_charges,
                'currency' => $collection->currency,
                'base_debit' => 0,
                'base_credit' => $collection->base_other_charges,
                'base_currency' => 'SAR',
                'exchange_rate' => $collection->currency_rate,
                'job_id' => $collection->job_id ?? null,
                'job_no' => $collection->job_no ?? '',
                'cost_center_id' => null,
                'is_tax_line' => 0,
                'is_auto_generated' => 1,
                'linked_id' => $collection->id,
                'linked_type' => Collection::class,
                'user_id' => Auth::id(),
                'company_id' => $collection->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Calculate and add currency exchange difference if any
        foreach ($collectionInvoices as $collectionInvoice) {
            $invoice = $collectionInvoice->customerInvoice;

            // Skip if invoice currency is the same as base currency
            if ($invoice->currency === 'SAR') {
                continue;
            }

            // Get the original exchange rate from the invoice
            $originalRate = $invoice->currency_rate ?? 1;
            $currentRate = $collection->currency_rate ?? 1;

            // Skip if rates are the same
            if (abs($originalRate - $currentRate) < 0.0001) {
                continue;
            }

            // Calculate the exchange difference
            $amountInForeignCurrency = $collectionInvoice->amount;
            $originalAmountInBase = $amountInForeignCurrency * $originalRate;
            $currentAmountInBase = $amountInForeignCurrency * $currentRate;
            $exchangeDifference = $currentAmountInBase - $originalAmountInBase;

            // Skip if difference is negligible
            if (abs($exchangeDifference) < 0.01) {
                continue;
            }

            // Add entry for currency exchange difference
            // Account ID 60 is assumed to be the Currency Exchange Difference account
            // If it doesn't exist, you'll need to create it
            $financeSubs[] = [
                'finance_id' => $finance->id,
                'voucher_no' => $finance->voucher_no,
                'voucher_type' => $finance->voucher_type,
                'reference_no' => $invoice->row_no,
                'customer_id' => $collection->customer_id,
                'account_id' => 60, // Currency Exchange Difference account
                'reference_date' => formDate($collection->collection_date),
                'description' => 'Currency exchange difference for invoice ' . $invoice->row_no,
                'debit' => $exchangeDifference < 0 ? abs($exchangeDifference) : 0,
                'credit' => $exchangeDifference > 0 ? $exchangeDifference : 0,
                'currency' => 'SAR', // Always in base currency
                'base_debit' => $exchangeDifference < 0 ? abs($exchangeDifference) : 0,
                'base_credit' => $exchangeDifference > 0 ? $exchangeDifference : 0,
                'base_currency' => 'SAR',
                'exchange_rate' => 1, // Base currency to base currency
                'job_id' => $invoice->job_id ?? null,
                'job_no' => $invoice->job_no ?? '',
                'cost_center_id' => null,
                'is_tax_line' => 0,
                'is_auto_generated' => 1,
                'linked_id' => $collectionInvoice->id,
                'linked_type' => CollectionInvoice::class,
                'user_id' => Auth::id(),
                'company_id' => $collection->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all finance sub entries
        FinanceSub::insert($financeSubs);
    }

    /**
     * Delete finance entries for a collection.
     *
     * @param int $collectionId
     * @return void
     */
    private function deleteFinanceEntries($collectionId)
    {
        $collection = Collection::findOrFail($collectionId);

        // Get finance entries using the relationship and delete them
        // This will also delete related finance sub entries due to the cascade delete in the database
        foreach ($collection->financeEntries as $finance) {
            // Delete finance sub entries first
            $finance->financeSubs()->delete();

            // Then delete the finance entry
            $finance->delete();
        }
    }
}
