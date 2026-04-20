<?php

namespace App\Http\Controllers\Finance\Expense;

use App\Enums\ExpenseEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Document;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\Expense\ExpenseSub;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Master\Description;
use App\Models\Master\Unit;
use App\Models\Supplier\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];
        $rows = Expense::with(['vendor:id,name_en,name_ar,row_no', 'customer:id,name_en,name_ar,row_no'])
            ->with(['job:id,shipment_mode'])// eager load customer
            ->when($request->tab, function ($q) use ($request) {
                $q->where('expenses.status', ExpenseEnum::fromName($request->tab));
            })
            ->when(isset($filter['filter-from-date']) && isset($filter['filter-to-date']), function ($query) use ($filter) {
                $query->whereBetween('posted_at', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($filter['suppliers']) && !empty($filter['suppliers']), function ($query) use ($filter) {
                $query->whereIn('vendor_id', decodeIds($filter['suppliers']));
            })
            ->orderBy('expenses.id', 'desc');

        // ✅ Get counts per status
        $statusCounts = Expense::select('status', DB::raw('COUNT(*) as total'))
            ->when(isset($filter['filter-from-date']) && isset($filter['filter-to-date']), function ($query) use ($filter) {
                $query->whereBetween('posted_at', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($filter['suppliers']) && !empty($filter['suppliers']), function ($query) use ($filter) {
                $query->whereIn('vendor_id', decodeIds($filter['suppliers']));
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $allCounts = [];
        foreach (ExpenseEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }

        $decimals = decimals();
        // ✅ Return formatted DataTable
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Expense #' . htmlspecialchars($model->invoice_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'expense-' . strtolower($model->invoice_no ?? $model->id),
            ])
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('customer_name', fn($model) => $model->customer?->name_en ?? '-')
            ->editColumn('base_total', fn($model) => number_format($model->base_tax_total + $model->base_sub_total, $decimals))
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, $decimals))
            ->with([
                'statusCounts' => $allCounts,
            ])
            ->toJson();
    }

    public function modal(Request $request)
    {
        $expense = new Expense();
        $expense->expenseSubs = [new ExpenseSub()];
        //$accounts = Account::where('type', 'Expense')->get();
        $descriptions = Description::descriptions();
        $units = Unit::all();

        $parents = Account::where('type', 'Expense')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', 'Expense')->where('is_grouped', 0)->orderBy('name')->get();

        $mainParents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $mainSubAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        return view('modules.finance.expense.expense-form', compact(
            'expense', 'parents', 'subAccounts', 'descriptions', 'units', 'mainParents', 'mainSubAccounts'
        ));
    }

    public function edit(Request $request, $id)
    {
        $expense = Expense::with(['expenseSubs', 'documents'])->findOrFail($id);
        $suppliers = Supplier::where('status', 'active')->get();
        $customers = Customer::where('status', 'active')->get();
        //$accounts = Account::where('type', 'Expense')->get();
        $descriptions = Description::all();
        $units = Unit::all();

        $parents = Account::where('type', 'Expense')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', 'Expense')->where('is_grouped', 0)->orderBy('name')->get();

        $mainParents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $mainSubAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        return view('modules.finance.expense.expense-form', compact(
            'expense', 'parents', 'subAccounts', 'suppliers', 'customers', 'descriptions', 'units', 'mainParents', 'mainSubAccounts'
        ));
    }

    public function store(Request $request)
    {
        $id = $request->input('data-id');

        if ($request->has('unit_price')) {
            $request->merge([
                'unit_price' => collect($request->unit_price)
                    ->map(fn($v) => str_replace(',', '', $v))
                    ->toArray()
            ]);
        }
        $request->merge(['supplier' => decodeId($request->input('supplier'))]);
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $request->merge([
            'item_ids' => decodeIds($request->item_id)
        ]);
        $companyId = companyId();
        $branchId = 1;

        $subTotal = 0;
        $taxTotal = 0;

        foreach ($request->quantity as $i => $qty) {
            $price = $request->unit_price[$i] ?? 0;
            $taxRate = vatPercent($request->tax[$i] ?? 0);

            $lineTotal = amountToFloat($qty) * amountToFloat($price);
            $lineTax = $lineTotal * ($taxRate / 100);

            $subTotal += $lineTotal;
            $taxTotal += $lineTax;
        }

        $grandTotal = $subTotal + $taxTotal;

        DB::beginTransaction();
        try {
            $expenseData = [
                'posted_at' => $request->input('posted_at'),
                'vendor_id' => $request->input('supplier'),
                'customer_id' => $request->input('customer'),
                'job_id' => $request->input('job_id'),
                //'expense_category_id' => $request->input('expense_category_id'),
                'reference_number' => $request->input('reference_number'),
                'amount_excluding_vat' => $request->input('amount_excluding_vat', 0),
                /*'vat_rate' => $request->input('vat_rate', 0),
                'vat_amount' => $request->input('vat_amount', 0),*/
                'amount_including_vat' => $request->input('amount_including_vat', 0),
                'payment_status' => $request->input('payment_status', 'unpaid'),
                'payment_mode' => $request->input('main_account'),
                'paid_amount' => $request->input('paid_amount', 0),
                'is_billable' => $request->input('is_billable', 0),
                'status' => ExpenseEnum::PENDING->value,
                //'created_by' => $id ? Expense::find($id)->created_by : Auth::id(),
                'currency' => $request->input('currency'),
                'currency_rate' => $request->input('currency_rate'),
                'base_sub_total' => $request->input('currency_rate') * $subTotal,
                'grand_total' => $request->input('currency_rate') * $taxTotal,
            ];

            if ($id) {
                $expense = Expense::findOrFail($id);
                $expense->update($expenseData);
                $expense->expenseSubs()->delete(); // Remove old items
            } else {
                $lastNumber = Expense::where('company_id', $companyId)->max('unique_number');
                $row_number = ($lastNumber ?? 0) + 1;
                $expenseData = array_merge($expenseData, [
                    'unique_number' => $row_number,
                    'row_no' => 'EXP-' . str_pad($row_number, 5, '0', STR_PAD_LEFT),
                    'user_id' => Auth::id(),
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                ]);
                $expense = Expense::create($expenseData);
            }

            // Process expense items
            //$descriptionIds = $request->input('description_id', []);
            $employeeIds = $request->input('employee_id', []);
            $accounts = $request->input('account', []);
            $quantities = $request->input('quantity', []);
            $rates = $request->input('unit_price', []);
            $vatRates = $request->input('tax', []);
            $vatRates = $request->input('tax', []);

            foreach ($accounts as $index => $account) {
                if (!$account) continue;
                $employee = $request->employee_id[$index] ?? 0;
                $quantity = $request->quantity[$index] ?? 0;
                $rate = $request->unit_price[$index] ?? 0;
                $taxRate = vatPercent($request->tax[$index] ?? 0);
                $lineTotal = amountToFloat($quantity) * amountToFloat($rate);
                $lineTax = $lineTotal * ($taxRate / 100);
                $netAmount = $lineTotal + $lineTax;
                ExpenseSub::create([
                    'expense_id' => $expense->id,
                    //'description_id' => $descriptionId,
                    'employee_id' => $employee,
                    'account_id' => $account,
                    'comment' => $request->comment[$index] ?? null,
                    'quantity' => amountToFloat($quantity),
                    'unit_price' => amountToFloat($rate),
                    'line_total' => $lineTotal,
                    'tax_percent' => $taxRate,
                    'tax_code' => $request->tax[$index] ?? null,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                    'total_with_tax' => $netAmount,
                ]);
            }

            // Handle attachments
            /*if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('documents/' . Auth::id() . '/expense/' . $expense->id, 'public');

                    Document::create([
                        'documentable_type' => Expense::class,
                        'documentable_id' => $expense->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                        'posted_date' => now(),
                        'posted_by' => Auth::id()
                    ]);
                }
            }*/

            if ($request->hasFile('attachments') && count($request->file('attachments'))) {
                $userId = Auth::id();

                foreach ($request->file('attachments') as $file) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // file name without extension
                    $extension = $file->getClientOriginalExtension(); // file extension
                    $uniqueName = $originalName . '_' . uniqid() . '.' . $extension; // append unique ID

                    // Store file using unique name
                    $path = $file->storeAs(
                        'documents/' . $expense->company_id . '/expense/' . $expense->id,
                        $uniqueName,
                        'public'
                    );

                    // Save record in DB
                    $expense->documents()->create([
                        'document_type' => Expense::class,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(), // keep original name for display
                        'title' => 'expense',
                        'posted_date' => now(),
                        'user_id' => $userId,
                        'company_id' => $expense->company_id,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier invoice created successfully',
                'customer_id' => $expense->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function actions($id)
    {
        $expense = Expense::select(
            'id',
            'row_no',
            'status'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];

        $actions = [];

        if ($expense->status === ExpenseEnum::PENDING->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approve'),
                        'code' => '01CSED',
                        'id' => 'row_approved',
                        'class' => 'row_approved',
                        'data-id' => $expense->id,
                        'type' => 'item',
                        'data-value' => 2,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancel'),
                        'code' => '01CSED',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $expense->id,
                        'type' => 'item',
                        'data-value' => 3,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($expense->status === ExpenseEnum::APPROVED->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Draft'),
                        'code' => '01CSED',
                        'id' => 'row_draft',
                        'class' => 'row_draft',
                        'data-id' => $expense->id,
                        'type' => 'item',
                        'data-value' => 1,
                        'icon' => 'pending'
                    ],
                ]
            ]);
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $expense->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'EXPENSE.printPreview(' . $expense->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $expense->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => $expense->status == ExpenseEnum::CANCELLED->value ? 'none' : 'after',
        ]);

        if ($expense->status !== ExpenseEnum::CANCELLED->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $expense->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $expense->id,
                'type' => 'item',
                'icon' => 'delete'
            ];

            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit, $delete]
            ]);
        }


        return response()->json($contextMenu->values());
    }

    public function updateStatus(Request $request, $id, $status)
    {
        $expense = Expense::findOrFail($id);
        $previousStatus = $expense->status;

        DB::beginTransaction();
        try {
            $expense->status = $status;
            $expense->save();

            // Create finance entries when expense is approved
            if ($status == ExpenseEnum::APPROVED->value) {
                $this->createExpenseFinanceEntries($expense);
            }

            // Delete finance entries when expense is moved from APPROVED to another status
            if ($previousStatus == ExpenseEnum::APPROVED->value && $status != ExpenseEnum::APPROVED->value) {
                $this->deleteExpenseFinanceEntries($expense->id);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Expense status updated successfully!',
                'data' => [
                    'id' => $expense->id,
                    'status' => $expense->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating expense status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function overview($id)
    {
        $expense = Expense::with(['vendor', 'customer', 'job', /*'branch', 'expenseCategory',*/ 'expenseSubs.account', 'documents'])
            ->findOrFail($id);

        return view('modules.finance.expense.view-overview', compact('expense'));
    }

    public function print($id)
    {
        $expense = Expense::with(['vendor', 'customer', 'job', /*'branch', 'expenseCategory', */ 'expenseSubs.account'])
            ->findOrFail($id);

        return view('modules.finance.expense.expense-print', compact('expense'));
    }

    public function destroy($id)
    {
        try {
            $expense = Expense::findOrFail($id);

            // Delete related documents
            foreach ($expense->documents as $document) {
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }

            // Delete expense items
            $expense->expenseSubs()->delete();

            // Delete the expense
            $expense->delete();

            return response()->json(['success' => true, 'message' => 'Expense deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create finance entries for an expense.
     *
     * @param Expense $expense
     * @return void
     */
    private function createExpenseFinanceEntries(Expense $expense)
    {
        try {
            // Delete any existing finance entries for this expense
            $this->deleteExpenseFinanceEntries($expense->id);

            // Create a new finance entry
            $finance = new Finance();
            $finance->voucher_no = $expense->row_no;
            $finance->voucher_type = 'EV'; // Expense Voucher
            $finance->reference_no = $expense->reference_no ?? $expense->row_no;
            $finance->reference_date = $expense->posted_at;
            $finance->supplier_id = $expense->vendor_id;
            $finance->customer_id = $expense->customer_id;
            $finance->narration = 'Expense: ' . $expense->row_no;
            $finance->currency = $expense->currency ?? 'SAR';
            $finance->exchange_rate = $expense->currency_rate ?? 1;
            $finance->total_debit = $expense->grand_total ?? 0;
            $finance->total_credit = $expense->grand_total ?? 0;
            $finance->base_currency = 'SAR'; // Assuming SAR is the base currency
            $finance->base_total_debit = $expense->base_grand_total ?? 0;
            $finance->base_total_credit = $expense->base_grand_total ?? 0;
            $finance->job_id = $expense->job_id ?? 0;
            $finance->job_no = $expense->job_no ?? '';
            $finance->is_approved = 1; // Approved
            $finance->posted_at = now();
            $finance->linked_id = $expense->id;
            $finance->linked_type = Expense::class;
            $finance->company_id = $expense->company_id;
            $finance->user_id = Auth::id();
            $finance->save();

            // Get expense subs
            $expenseSubs = $expense->expenseSubs;

            // Create finance sub entries
            $financeSubs = [];

            // Credit entry for the bank/cash account
            $financeSubs[] = [
                'finance_id' => $finance->id,
                'voucher_no' => $finance->voucher_no,
                'voucher_type' => $finance->voucher_type,
                'reference_no' => $finance->reference_no,
                'supplier_id' => $expense->vendor_id,
                'customer_id' => $expense->customer_id,
                'account_id' => $expense->payment_mode == 'bank' ? 4 : 3, // 4 for Bank, 3 for Cash
                'reference_date' => formDate($expense->posted_at),
                'description' => 'Payment for Expense ' . $expense->row_no,
                'debit' => 0,
                'credit' => $expense->grand_total,
                'currency' => $expense->currency ?? 'SAR',
                'base_debit' => 0,
                'base_credit' => $expense->base_grand_total ?? $expense->grand_total,
                'base_currency' => 'SAR',
                'exchange_rate' => $expense->currency_rate ?? 1,
                'job_id' => $expense->job_id ?? null,
                'job_no' => $expense->job_no ?? '',
                'cost_center_id' => null,
                'is_tax_line' => 0,
                'is_auto_generated' => 1,
                'linked_id' => $expense->id,
                'linked_type' => Expense::class,
                'user_id' => Auth::id(),
                'company_id' => $expense->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Debit entries for each expense sub
            foreach ($expenseSubs as $expenseSub) {
                $financeSubs[] = [
                    'finance_id' => $finance->id,
                    'voucher_no' => $finance->voucher_no,
                    'voucher_type' => $finance->voucher_type,
                    'reference_no' => $finance->reference_no,
                    'supplier_id' => $expense->vendor_id,
                    'customer_id' => $expense->customer_id,
                    'account_id' => $expenseSub->account_id,
                    'reference_date' => formDate($expense->posted_at),
                    'description' => $expenseSub->description ?? 'Expense Item',
                    'debit' => $expenseSub->total,
                    'credit' => 0,
                    'currency' => $expense->currency ?? 'SAR',
                    'base_debit' => $expenseSub->total * ($expense->currency_rate ?? 1),
                    'base_credit' => 0,
                    'base_currency' => 'SAR',
                    'exchange_rate' => $expense->currency_rate ?? 1,
                    'job_id' => $expense->job_id ?? null,
                    'job_no' => $expense->job_no ?? '',
                    'cost_center_id' => null,
                    'is_tax_line' => 0,
                    'is_auto_generated' => 1,
                    'linked_id' => $expenseSub->id,
                    'linked_type' => ExpenseSub::class,
                    'user_id' => Auth::id(),
                    'company_id' => $expense->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Calculate and add currency exchange difference if any
            if ($expense->currency !== 'SAR' && $expense->currency_rate > 0) {
                // Get the original exchange rate (if this is an update, it might have changed)
                $originalRate = $expense->getOriginal('currency_rate') ?? $expense->currency_rate;
                $currentRate = $expense->currency_rate;

                // Skip if rates are the same
                if (abs($originalRate - $currentRate) > 0.0001) {
                    // Calculate the exchange difference
                    $amountInForeignCurrency = $expense->grand_total;
                    $originalAmountInBase = $amountInForeignCurrency * $originalRate;
                    $currentAmountInBase = $amountInForeignCurrency * $currentRate;
                    $exchangeDifference = $currentAmountInBase - $originalAmountInBase;

                    // Skip if difference is negligible
                    if (abs($exchangeDifference) > 0.01) {
                        // Add entry for currency exchange difference
                        // Account ID 60 is assumed to be the Currency Exchange Difference account
                        $financeSubs[] = [
                            'finance_id' => $finance->id,
                            'voucher_no' => $finance->voucher_no,
                            'voucher_type' => $finance->voucher_type,
                            'reference_no' => $finance->reference_no,
                            'supplier_id' => $expense->vendor_id,
                            'customer_id' => $expense->customer_id,
                            'account_id' => 60, // Currency Exchange Difference account
                            'reference_date' => formDate($expense->posted_at),
                            'description' => 'Currency exchange difference for expense ' . $expense->row_no,
                            'debit' => $exchangeDifference > 0 ? $exchangeDifference : 0,
                            'credit' => $exchangeDifference < 0 ? abs($exchangeDifference) : 0,
                            'currency' => 'SAR', // Always in base currency
                            'base_debit' => $exchangeDifference > 0 ? $exchangeDifference : 0,
                            'base_credit' => $exchangeDifference < 0 ? abs($exchangeDifference) : 0,
                            'base_currency' => 'SAR',
                            'exchange_rate' => 1, // Base currency to base currency
                            'job_id' => $expense->job_id ?? null,
                            'job_no' => $expense->job_no ?? '',
                            'cost_center_id' => null,
                            'is_tax_line' => 0,
                            'is_auto_generated' => 1,
                            'linked_id' => $expense->id,
                            'linked_type' => Expense::class,
                            'user_id' => Auth::id(),
                            'company_id' => $expense->company_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // Insert all finance sub entries
            FinanceSub::insert($financeSubs);
        } catch (\Exception $e) {
            Log::error('Error creating finance entries for expense: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete finance entries for an expense.
     *
     * @param int $expenseId
     * @return void
     */
    private function deleteExpenseFinanceEntries($expenseId)
    {
        try {
            // Find all finance entries linked to this expense
            $financeEntries = Finance::where('linked_id', $expenseId)
                ->where('linked_type', Expense::class)
                ->get();

            // Delete each finance entry and its sub entries
            foreach ($financeEntries as $finance) {
                // Delete finance sub entries first
                FinanceSub::where('finance_id', $finance->id)->delete();

                // Then delete the finance entry
                $finance->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error deleting finance entries for expense: ' . $e->getMessage());
            throw $e;
        }
    }
}
