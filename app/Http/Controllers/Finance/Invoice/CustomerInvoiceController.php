<?php

namespace App\Http\Controllers\Finance\Invoice;

use App\Services\DocumentNumber;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\JobEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Zatca\ZatcaController;
use App\Models\Finance\Account\Account;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceSub;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Job\Job;
use App\Models\Master\Bank;
use App\Models\Master\Description;
use App\Models\Master\LogisticActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class CustomerInvoiceController extends Controller
{

    public function modal(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $customer = new CustomerInvoice();
        $customer->customerInvoiceSubs = [new CustomerInvoiceSub()];
        $job_customer_id = $job_id = null;
        if ($request->get('jobId') == 'list') {
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->whereIn('status', [JobEnum::COMPLETED, JobEnum::PENDING])->get();
        } else {
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->findOrFail(decodeId($request->get('jobId')));
            $job_customer_id = $jobs->customer_id;
            $job_id = $jobs->id;
            $jobs = [$jobs];
        }

        //$accounts = Account::where('type', '!=', 'Equity')->where('is_active', 1)->get();
        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();
        return view('modules.finance.customer-invoice.customer-invoice-form', compact('customer', 'jobs', 'parents', 'subAccounts', 'job_customer_id', 'job_id'));
    }

    /** Full-page invoice builder: a Sale | Cost | GP grid per line (cost feeds the margin only, it is not posted). */
    public function newPage(Request $request)
    {
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en,currency')
            ->whereIn('status', [JobEnum::COMPLETED, JobEnum::PENDING])->orderByDesc('id')->get();
        $descriptions = DB::table('descriptions')
            ->where(fn ($q) => $q->where('company_id', companyId())->orWhereNull('company_id'))
            ->orderBy('description')->get(['id', 'description', 'sale_account_id']);
        $units = \App\Models\Master\Unit::units([]);
        $taxes = collect(vat())->map(fn ($v) => ['code' => $v['code'], 'percent' => (float) $v['percent'], 'label' => number_format($v['percent'], 2) . '% - ' . __($v['label'])])->values();
        $currencies = currencies();
        $selectedJob = $request->filled('jobId') ? decodeId($request->get('jobId')) : null;

        return view('modules.finance.customer-invoice.customer-invoice-new', compact('jobs', 'descriptions', 'units', 'taxes', 'currencies', 'selectedJob'));
    }

    public function edit($id)
    {
        $customer = CustomerInvoice::with(['customerInvoiceSubs', 'documents'])->findOrFail($id);
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->get();

        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts
        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();

        $job_customer_id = $customer->customer_id;
        $job_id = $customer->job_id;

        return view('modules.finance.customer-invoice.customer-invoice-form', compact('customer', 'jobs', 'parents', 'subAccounts', 'job_customer_id', 'job_id'));
    }

    public function listBasedOnJob($job_id)
    {
        $job = Job::select('row_no')->find(decodeId($job_id));
        $job_no = $job->row_no;
        return view('modules.finance.customer-invoice.list', compact('job_id', 'job_no'));
    }

    /**
     * Applies the Status / Payment Status / Overdue filters shared by the
     * rows and tab-count queries below.
     */
    private function applyExtraFilters($query, array $filter)
    {
        // "all" is the sentinel value for the "All Status"/"All Payment" dropdown
        // option, meaning no filter should be applied — not a literal status to match.
        $statusFilter = array_filter((array) ($filter['filter-status'] ?? []), fn($v) => $v !== 'all' && $v !== '');
        $paymentStatusFilter = array_filter((array) ($filter['filter-payment-status'] ?? []), fn($v) => $v !== 'all' && $v !== '');

        return $query
            ->when(!empty($statusFilter), function ($q) use ($statusFilter) {
                $q->whereIn('customer_invoices.status', $statusFilter);
            })
            ->when(!empty($paymentStatusFilter), function ($q) use ($paymentStatusFilter) {
                $statuses = $paymentStatusFilter;
                $q->where(function ($group) use ($statuses) {
                    foreach ($statuses as $status) {
                        $group->orWhere(function ($sub) use ($status) {
                            if ($status === 'paid') {
                                $sub->where('grand_total', '>', 0)->whereColumn('paid_amount', '>=', 'grand_total');
                            } elseif ($status === 'partial') {
                                $sub->where('paid_amount', '>', 0)->whereColumn('paid_amount', '<', 'grand_total');
                            } elseif ($status === 'unpaid') {
                                $sub->where(function ($u) {
                                    $u->whereNull('paid_amount')->orWhere('paid_amount', '<=', 0);
                                });
                            }
                        });
                    }
                });
            })
            ->when(($filter['filter-overdue'] ?? 'all') === 'overdue', function ($q) {
                $q->where('due_at', '<', now())->whereColumn('paid_amount', '<', 'grand_total');
            })
            ->when(($filter['filter-overdue'] ?? 'all') === 'non_due', function ($q) {
                $q->where('due_at', '>=', now())->whereColumn('paid_amount', '<', 'grand_total');
            });
    }

    /**
     * Applies the active list tab (All / Draft / Approved / Overdue Invoice /
     * Credit Note / Cancelled). Unlike status, "Overdue Invoice" and "Credit
     * Note" aren't CustomerInvoiceEnum values — overdue is a payment-based
     * condition, and credit notes are a separate related table.
     */
    private function applyTabFilter($query, ?string $tab)
    {
        $tab = strtolower((string) $tab);

        return match ($tab) {
            '', 'all' => $query,
            'overdue' => $query->where('due_at', '<', now())->whereColumn('paid_amount', '<', 'grand_total'),
            'creditnote' => $query->whereExists(function ($sub) {
                $sub->selectRaw(1)->from('credit_notes')
                    ->whereColumn('credit_notes.invoice_id', 'customer_invoices.id');
            }),
            default => $query->where('customer_invoices.status', CustomerInvoiceEnum::fromName($tab)),
        };
    }

    public function fetchAllRows(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        if ($job_id != 'list') {
            $job_id = decodeId($job_id);
        }
        $filter = $request->filterData ?? [];
        $rows = CustomerInvoice::select(
            'row_no',
            'customer_invoices.id as id',
            'due_at',
            'invoice_date',
            'job_id',
            'job_no',
            'customer_id',
            'tax_submit_status',
            'customer_invoices.currency as currency',
            'currency_rate',
            'base_sub_total',
            'sub_total',
            'base_tax_total',
            'tax_total',
            'grand_total',
            'paid_amount',
            'customer_invoices.status as status',
            'customer_invoices.created_at as created_at',
            'customer_invoices.company_id as company_id',
        )
            ->with(['customer:id,name_en,name_ar,row_no'])
            ->with(['job:id,shipment_mode,pol,pod,activity_id,carrier,shipment_mode'])// eager load customer
            ->tap(fn($query) => $this->applyTabFilter($query, $request->tab))
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {

                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('invoice_date', '>=', $from)
                        ->where('invoice_date', '<', $to);
                }
            )
            ->when(isset($filter['customSearch']) && !empty($filter['customSearch']), function ($query) use ($filter) {
                $search = $filter['customSearch'];
                $query->where(function ($q) use ($search) {
                    $q->where('row_no', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                        ->orWhereHas('job', function ($q) use ($search) {
                            $q->where('pol', 'like', "%{$search}%")
                                ->orWhere('pod', 'like', "%{$search}%");
                        });
                });
            })
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->tap(fn($query) => $this->applyExtraFilters($query, $filter))
            ->orderBy('customer_invoices.id', 'desc');

        // ✅ Counts for the All / Draft / Approved / Overdue Invoice / Credit
        // Note / Cancelled tabs — each reuses the same shared (non-tab)
        // filters so every tab's count reflects the current date/customer/
        // search filters, matching what its own tab click would return.
        $baseCountQuery = function () use ($job_id, $filter, $request) {
            return CustomerInvoice::query()
                ->when($job_id != 'list', function ($query) use ($job_id) {
                    $query->where('job_id', $job_id);
                })
                ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                    function ($query) use ($filter) {
                        $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                        $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                        $query->where('invoice_date', '>=', $from)
                            ->where('invoice_date', '<', $to);
                    }
                )
                ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                    $query->whereIn('customer_id', decodeIds($filter['customers']));
                })
                ->when(isset($request->filterData['customSearch']) && !empty($request->filterData['customSearch']), function ($query) use ($request) {
                    $search = $request->filterData['customSearch'];
                    $query->where(function ($q) use ($search) {
                        $q->where('row_no', 'like', "%{$search}%")
                            ->orWhere('job_no', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($q) use ($search) {
                                $q->where('name_en', 'like', "%{$search}%")
                                    ->orWhere('name_ar', 'like', "%{$search}%");
                            })
                            ->orWhereHas('job', function ($q) use ($search) {
                                $q->where('pol', 'like', "%{$search}%")
                                    ->orWhere('pod', 'like', "%{$search}%");
                            });
                    });
                })
                ->tap(fn($query) => $this->applyExtraFilters($query, $filter));
        };

        $allCounts = [
            'all' => $baseCountQuery()->count(),
            'draft' => $baseCountQuery()->where('status', CustomerInvoiceEnum::DRAFT->value)->count(),
            'approved' => $baseCountQuery()->where('status', CustomerInvoiceEnum::APPROVED->value)->count(),
            'overdue' => $baseCountQuery()->where('due_at', '<', now())->whereColumn('paid_amount', '<', 'grand_total')->count(),
            'creditnote' => $baseCountQuery()->whereExists(function ($sub) {
                $sub->selectRaw(1)->from('credit_notes')
                    ->whereColumn('credit_notes.invoice_id', 'customer_invoices.id');
            })->count(),
            'cancelled' => $baseCountQuery()->where('status', CustomerInvoiceEnum::CANCELLED->value)->count(),
        ];

        // Summary card totals (Draft / Approved) in company currency, using
        // the same filters as the list.
        $draft = CustomerInvoiceEnum::DRAFT->value;
        $approved = CustomerInvoiceEnum::APPROVED->value;
        $salesSummary = $baseCountQuery()->select([
            DB::raw("SUM(CASE WHEN status = {$draft} THEN base_grand_total ELSE 0 END) as total_draft_grand"),
            DB::raw("SUM(CASE WHEN status = {$draft} THEN base_sub_total ELSE 0 END) as total_draft_sub"),
            DB::raw("SUM(CASE WHEN status = {$draft} THEN base_tax_total ELSE 0 END) as total_draft_tax"),
            DB::raw("SUM(CASE WHEN status = {$approved} THEN base_grand_total ELSE 0 END) as total_approved_grand"),
            DB::raw("SUM(CASE WHEN status = {$approved} THEN base_sub_total ELSE 0 END) as total_approved_sub"),
            DB::raw("SUM(CASE WHEN status = {$approved} THEN base_tax_total ELSE 0 END) as total_approved_tax"),
        ])->first();
        $decimals = decimals();
        $activity = LogisticActivity::activities();
        // ✅ Return formatted DataTable
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Customer Invoice ' . htmlspecialchars((int) $model->status === 1 ? 'DRAFT' : $model->invoice_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'customer-invoice-' . strtolower($model->invoice_no ?? $model->id),
            ])
            ->editColumn('invoice_date', fn($model) => \Carbon\Carbon::parse($model->invoice_date)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('customer_name', fn($model) => $model->customer?->name_en ? truncateName($model->customer?->name_en, 15) : '-')
            ->addColumn('due_status', fn($model) => 'unpaid')
            ->addColumn('job_activity', fn($model) => $model->job ? ($activity->where('id', $model->job->activity_id)->pluck('name')->first() ?? '-') : '-')
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, $decimals))
            ->editColumn('invoice_date', fn($model) => Carbon::parse($model->invoice_date)->translatedFormat('d-M-Y'))
            ->editColumn('due_at', fn($model) => Carbon::parse($model->due_at)->translatedFormat('d-M-Y'))
            ->addColumn('due_days', function ($model) {
                $now = now()->startOfDay();
                $dueAt = Carbon::parse($model->due_at)->startOfDay();

                $diff = $now->diffInDays($dueAt, false);
                $class = 'bg-danger-subtle text-danger border border-danger';
                if ($diff < 0) {
                    $mess = 'OVERDUE: ' . abs($diff) . ' DAYS';
                } elseif ($diff == 0) {
                    $mess = 'DUE: TODAY';
                } elseif ($diff == 1) {
                    $mess = 'DUE: TOMORROW';
                } else {
                    $mess = 'OPEN: ' . $diff . ' DAYS LEFT';
                    $class = 'bg-primary-subtle text-primary border-primary';
                }
                return [
                    'label' => $mess,
                    'class' => $class
                ];
            })
            ->addColumn('balance', function ($model) use ($decimals) {
                $balance = $model->grand_total - ($model->paid_amount ?? 0);
                return number_format($balance, $decimals);
            })
            /*->editColumn('created_at', fn($model) => \Carbon\Carbon::parse($model->created_at)->format('d-m-Y'))*/
            ->with([
                'statusCounts' => $allCounts,
                'salesSummary' => $salesSummary,
            ])
            ->toJson();
    }

    public function store(Request $request)
    {
        // Remove commas from all unit prices
        if ($request->has('unit_price')) {
            $request->merge([
                'unit_price' => collect($request->unit_price)
                    ->map(fn($v) => str_replace(',', '', $v))
                    ->toArray()
            ]);
        }
        $request->merge(['customer' => decodeId($request->input('customer'))]);

        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'customer' => ['required', Rule::exists('customers', 'id')->where('company_id', companyId())],
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'currency_rate' => 'required',
            'currency' => 'required|exists:currencies,code',
            'terms' => 'nullable|string|max:1000',

            'description_id.*' => 'required|string|max:255',
            'comment.*' => 'nullable|string|max:500',
            'unit_id.*' => 'required|numeric',
            'quantity.*' => 'required|numeric|min:1',
            'unit_price.*' => 'required|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tax.*' => 'nullable|string',
        ]);

        $this->assertPeriodOpen($validated['invoice_date'], 'invoice_date');

        //$userId = Auth::id();
        $companyId = companyId();

        // 🔹 Fetch or create new
        if ($request->filled('data-id')) {
            $customer = CustomerInvoice::findOrFail($request->input('data-id'));
        } else {
            $customer = new CustomerInvoice();

            // Draft number now (row_no input was removed from the form, and
            // this generator was disabled without a replacement, so every
            // new invoice saved with row_no = NULL). unique_row_no anchors
            // both this draft number and the invoice number assigned on
            // approval below, so the two stay related to the same sequence.
            // draft_no is read as well: an approved invoice keeps its old draft number there, and that number must not come back
            $prefix = 'DR-' . date('y') . '-';
            $customer->unique_row_no = DocumentNumber::next(CustomerInvoice::class, $prefix, ['draft_no']);
            $customer->row_no = $prefix . sprintf('%04d', $customer->unique_row_no);

            $job = Job::select('row_no')->find($request->input('job_id'));
            if ($job) {
                $customer->job_no = $job->row_no;
            }

            $this->setBaseColumns($customer);
        }

        // 🔹 Calculate totals
        $subTotal = 0;
        $taxTotal = 0;

        foreach ($request->quantity as $i => $qty) {
            $price = $request->unit_price[$i] ?? 0;
            $taxRate = vatPercent($request->tax[$i] ?? 0);

            $lineTotal = $qty * $price;
            $lineTax = round($lineTotal * ($taxRate / 100), 2);   // VAT is rounded per line, as on the ZATCA invoice

            $subTotal += $lineTotal;
            $taxTotal += $lineTax;
        }

        $grandTotal = $subTotal + $taxTotal;

        // 🔹 Assign totals
        // row_no is generated once above (new invoice) and never touched
        // again here — this used to unconditionally overwrite it with an
        // empty request field on every save, including edits of an
        // already-numbered draft.
        $customer->job_id = $validated['job_id'] ?? null;
        $customer->customer_id = $validated['customer'] ?? null;
        $customer->invoice_date = $validated['invoice_date'];
        $customer->due_at = $validated['due_date'];
        $customer->currency = $validated['currency'];
        $customer->currency_rate = $validated['currency_rate'];
        $customer->terms = $validated['terms'] ?? null;
        $customer->base_sub_total = $customer->currency_rate * $subTotal;
        $customer->base_tax_total = $customer->currency_rate * $taxTotal;
        $customer->sub_total = $subTotal;
        $customer->tax_total = $taxTotal;
        $customer->base_grand_total = $customer->base_sub_total + $customer->base_tax_total;
        $customer->grand_total = $grandTotal;
        $customer->status = 1;

        DB::beginTransaction();
        try {

        $customer->save();
        if ($request->hasFile('attachments') && count($request->file('attachments'))) {
            $userId = Auth::id();

            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // file name without extension
                $extension = $file->getClientOriginalExtension(); // file extension
                $uniqueName = $originalName . '_' . uniqid() . '.' . $extension; // append unique ID

                // Store file using unique name
                $path = $file->storeAs(
                    'documents/' . $companyId . '/customer_invoice/' . $customer->id,
                    $uniqueName,
                    'public'
                );

                // Save record in DB
                $customer->documents()->create([
                    'document_type' => CustomerInvoice::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(), // keep original name for display
                    'title' => 'customer_invoice',
                    'posted_date' => now(),
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ]);
            }
        }


        // 🔹 Prepare items
        $customerSub = [];
        $descriptions = Description::descriptions()->keyBy('id');
        foreach ($request->description_id as $i => $desc) {
            $qty = $request->quantity[$i] ?? 0;
            $price = $request->unit_price[$i] ?? 0;
            $taxRate = vatPercent($request->tax[$i] ?? 0);
            $lineTotal = $qty * $price;
            $lineTax = round($lineTotal * ($taxRate / 100), 2);   // VAT is rounded per line, as on the ZATCA invoice
            $netAmount = $lineTotal + $lineTax;

            $customerSub[] = [
                'customer_invoice_id' => $customer->id,
                'account_id' => $request->account[$i],
                'company_id' => $companyId,
                'description_id' => $desc,
                'description' => $descriptions[$desc]->description,
                'comment' => $request->comment[$i] ?? null,
                'unit_id' => $request->unit_id[$i],
                'quantity' => $qty,
                'unit_price' => $price,
                'base_unit_price' => $price * $customer->currency_rate,
                'tax_code' => $request->tax[$i] ?? null,
                'tax_percent' => $taxRate,
                'tax_amount' => $lineTax,
                'base_tax_amount' => $lineTax * $customer->currency_rate,
                'total' => $lineTotal,
                'base_total' => $lineTotal * $customer->currency_rate,
                'total_with_tax' => $netAmount,
                'base_total_with_tax' => $netAmount * $customer->currency_rate,
                // where the line was picked from (quotation / supplier invoice) and what it cost; null = typed by hand
                'source_type' => in_array($request->source_type[$i] ?? null, ['quotation', 'supplier_invoice'], true) ? $request->source_type[$i] : null,
                'source_id' => is_numeric($request->source_id[$i] ?? null) ? (int) $request->source_id[$i] : null,
                'source_sub_id' => is_numeric($request->source_sub_id[$i] ?? null) ? (int) $request->source_sub_id[$i] : null,
                'cost_base_amount' => is_numeric($request->cost_base_amount[$i] ?? null) ? (float) $request->cost_base_amount[$i] : null,
                'source_no' => null,
            ];
        }
        // a source line without its source document makes no sense: drop the half-filled link
        foreach ($customerSub as &$cs) {
            if (!$cs['source_type'] || !$cs['source_id']) {
                $cs['source_type'] = $cs['source_id'] = $cs['source_sub_id'] = $cs['cost_base_amount'] = null;
            }
        }
        unset($cs);

        // a picked supplier invoice must be an approved one of this job that no other customer invoice bills; keep its number on the line
        $links = app(\App\Services\Finance\SupplierInvoiceLinks::class);
        $bills = \App\Models\Finance\SupplierInvoice\SupplierInvoice::whereIn('id', collect($customerSub)->where('source_type', 'supplier_invoice')->pluck('source_id')->unique())
            ->get(['id', 'row_no', 'job_id', 'status'])->keyBy('id');
        $quotes = \App\Models\Quotation\Quotation::whereIn('id', collect($customerSub)->where('source_type', 'quotation')->pluck('source_id')->unique())
            ->get(['id', 'row_no', 'quotation_no'])->keyBy('id');
        foreach ($customerSub as &$cs) {
            if ($cs['source_type'] === 'quotation') {
                $q = $quotes[$cs['source_id']] ?? null;
                $cs['source_no'] = $q ? ($q->row_no ?: $q->quotation_no) : null;
            } elseif ($cs['source_type'] === 'supplier_invoice') {
                $b = $bills[$cs['source_id']] ?? null;
                $status = $b ? (int) ($b->status instanceof \BackedEnum ? $b->status->value : $b->status) : null;
                $other = $b ? $links->blockers($b->id, $customer->id) : [];
                if (!$b || (int) $b->job_id !== (int) $validated['job_id'] || $status !== \App\Enums\SupplierInvoiceEnum::APPROVED->value || $other) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $other
                            ? __('Supplier invoice :no is already billed in customer invoice :inv.', ['no' => $b->row_no, 'inv' => implode(', ', $other)])
                            : __('Only an approved supplier invoice of this job can be billed.'),
                    ], 422);
                }
                $cs['source_no'] = $b->row_no;
            }
        }
        unset($cs);
        // the invoice remembers the quotation it was built from
        $customer->quotation_id = collect($customerSub)->firstWhere('source_type', 'quotation')['source_id'] ?? null;
        $customer->save();

        DB::table('customer_invoice_subs')
            ->where('customer_invoice_id', $customer->id)
            ->delete();

        if (!empty($customerSub)) {
            DB::table('customer_invoice_subs')->insert($customerSub);
        }
        // the supplier lines now point at this invoice's lines (they are locked until this invoice is deleted)
        $links->sync($customer->id);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error saving customer Invoice: ') . $e->getMessage(),
            ], 500);
        }

        // Finance entries are created only on APPROVAL (in updateStatus), not on draft save

        return response()->json([
            'status' => 'success',
            'message' => 'customer invoice created successfully',
            'customer_id' => $customer->id,
        ]);
    }

    public function destroy($id)
    {
        $customer = CustomerInvoice::findOrFail($id);
        $this->deleteCustomerFinanceByRef($customer->invoice_number, 'SI');
        app(\App\Services\Finance\SupplierInvoiceLinks::class)->release((int) $id);
        $customer->delete();

        return response()->json(['status' => 'success', 'message' => __('Deleted successfully')]);
    }

    public function actions($id)
    {
        $customer = CustomerInvoice::select(
            'id',
            'row_no',
            'status'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        if ($customer->status === CustomerInvoiceEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approved'),
                        'code' => '01CSBK',
                        'id' => 'row_approved',
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($customer->status === CustomerInvoiceEnum::fromName('approved')) {
            /*$contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Convert To Invoice'),
                        'code' => '01CSEM',
                        'id' => 'row_converted',
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::CONVERTED->value,
                        'icon' => 'converted',
                        'separator' => 'after',
                    ],
                    [
                        'label' => __('Move To Draft'),
                        'code' => '01CSBK',
                        'id' => 'row_pending',
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::DRAFT->value,
                        'icon' => 'pending'
                    ]
                ]
            ]);*/
        }

        if ($customer->status === CustomerInvoiceEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'delete'
            ];
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'CUSTOMER_INVOICE.printPreview(' . $customer->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => 'after',
        ]);
        $contextMenu->push([
            'label' => __('Send Email'),
            'code' => '01CSEM',
            'id' => 'row_email',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'email',
            //'separator' => 'after',
        ]);

        // Record Payment / Payment History only apply to an approved invoice
        // being actively collected against — a draft has no finance entries
        // yet, so there is nothing to record a payment against.
        if ($customer->status === CustomerInvoiceEnum::APPROVED->value) {
            /*$contextMenu->push([
                'label' => __('Record Payment'),
                'code' => '01CSRP',
                'id' => 'row_record_payment',
                'class' => 'row_record_payment',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'payment',
                'separator' => 'before',
            ]);*/
            $contextMenu->push([
                'label' => __('Collection History'),
                'code' => '01CSPH',
                'id' => 'row_payment_history',
                'class' => 'row_payment_history',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'history',
            ]);
        }

        if ($customer->status === CustomerInvoiceEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit, $delete]
            ]);
        }
        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $customerInvoice = CustomerInvoice::findOrFail($id);
        $previousStatus = $customerInvoice->status;
        $message = 'Invoice status updated successfully!';
        if ($locked = $this->refuseInClosedPeriod($customerInvoice->getRawOriginal('invoice_date'), $previousStatus, $status, CustomerInvoiceEnum::APPROVED->value)) {
            return $locked;
        }

        DB::beginTransaction();
        try {
            if ($status == CustomerInvoiceEnum::APPROVED->value) {

                // checking zatca registered or not

                $zatca = zatcaDateCheck($customerInvoice);

                if ($zatca['zatcaRegister'] && $zatca['message']) {
                    return errorResponse($zatca['message']);
                }

                $postingDate = $customerInvoice->posting_date;
                if ($zatca['zatcaRegister']) {
                    $postingDate = date('Y-m-d');
                    $customerInvoice->old_invoice_date = formDate($customerInvoice->invoice_date);
                    $customerInvoice->invoice_date = $postingDate;
                }

                $customerInvoice->status = CustomerInvoiceEnum::APPROVED;

                $prefix = 'IN/' . date('y') . '/';
                $customerInvoice->unique_row_no = DocumentNumber::next(CustomerInvoice::class, $prefix);
                $customerInvoice->draft_no = $customerInvoice->row_no;
                $customerInvoice->row_no = $prefix . sprintf('%04d', $customerInvoice->unique_row_no);
                $customerInvoice->save();

                // Create finance entries when invoice is approved
                $this->createCustomerInvoiceFinanceEntries($customerInvoice);
                app(\App\Services\Finance\SupplierInvoiceLinks::class)->sync($customerInvoice->id);   // approval gave the invoice its real number

                $data = [];
                if ($zatca['zatcaRegister']) {
                    $customerInvoice->load(['customer']);
                    $zatca = new ZatcaController();
                    $z = $zatca->submitTax($customerInvoice);
                    $approveMessage = __("Zatca Approved", ['module' => '', 'status' => $z['title']]);

                    if ($z['type'] == 'error') {
                        return errorResponse([$z['message'], 'ZATCA Status - ' . $z['title']]);
                    } elseif ($z['type'] == 'warning') {
                        $data['type'] = 'warning';
                        $message = [
                            $approveMessage . ' with warning:' . $z['message'],
                            __($customerInvoice->title) . ' ' . $customerInvoice->row_no
                        ];
                    } else {
                        $message = [
                            $approveMessage,
                            __($customerInvoice->title) . ' ' . $customerInvoice->row_no
                        ];
                    }
                }
            } else {
                $customerInvoice->status = $status;
                $customerInvoice->save();
                // a cancelled invoice frees the supplier invoices it billed; any other status keeps them linked
                $links = app(\App\Services\Finance\SupplierInvoiceLinks::class);
                (int) $status === CustomerInvoiceEnum::CANCELLED->value ? $links->release($customerInvoice->id) : $links->sync($customerInvoice->id);

                // Delete finance entries when invoice is moved from APPROVED to another status
                if ($previousStatus == CustomerInvoiceEnum::APPROVED->value) {
                    $this->deleteCustomerInvoiceFinanceEntries($customerInvoice->id);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'id' => $customerInvoice->id,
                    'status' => $customerInvoice->status,
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error updating customer invoice status: ') . $e->getMessage(),
            ], 500);
        }
    }

    public function overview($id)
    {
        $customerInvoice = CustomerInvoice::with('customerInvoiceSubs', 'customer', 'job')->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        return view('modules.finance.customer-invoice.view-overview', compact('customerInvoice', 'descriptions'));
    }

    public function overviewDrawer($id)
    {
        $customerInvoice = CustomerInvoice::with('customerInvoiceSubs', 'customer', 'job')->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        $balance = (float) $customerInvoice->grand_total - (float) ($customerInvoice->paid_amount ?? 0);
        $transactions = $this->invoiceTransactions($id);
        // What settled this invoice, kept apart so it reads clearly: approved collections and approved credit notes
        $collected = (float) $transactions->where('type', 'Collection')->where('status_label', 'Approved')->sum('amount');
        $credited = (float) $transactions->where('type', 'Credit Note')->where('status_label', 'Approved')->sum('amount');
        // customer credit moved between invoices by a collection: + received here, - moved to another invoice
        $creditMoved = (float) $transactions->whereIn('type', ['Credit Applied', 'Credit Moved'])->sum('amount');

        [$origin, $timeline] = $this->invoiceTimeline($customerInvoice);

        return view('modules.finance.customer-invoice.view-overview-drawer', compact('customerInvoice', 'descriptions', 'balance', 'transactions', 'origin', 'timeline', 'collected', 'credited', 'creditMoved'));
    }

    /**
     * Time frame for the invoice drawer: how the invoice came to exist
     * (enquiry -> quotation -> job, or straight from a job), then its own
     * life (created / updated / approved / cancelled), credit notes
     * (partial or full) and collections, oldest first.
     */
    private function invoiceTimeline(CustomerInvoice $inv): array
    {
        $events = [];
        $rank = 0; // origin chain (enquiry -> quotation -> job) always comes first, in order
        $add = function ($label, $at, $icon, $by = null, $meta = null, $module = 'invoice', $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $ts = \Carbon\Carbon::parse($at);
            // Date-only values (credit note / collection dates) carry no time:
            // treat them as end of day so they follow same-day invoice events.
            $key = $ts->format('H:i:s') === '00:00:00' ? $ts->copy()->endOfDay()->timestamp : $ts->timestamp;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'by' => $by, 'meta' => $meta, 'module' => $module, 'link' => $link, 'rank' => $rank, 'key' => $key, 'seq' => count($events)];
        };

        $job = $inv->job_id ? \App\Models\Job\Job::find($inv->job_id) : null;
        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;

        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', null, null, 'enquiry', ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', null, null, 'quotation', ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', null, null, 'job', ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $quotation
            ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' . __('Job') . ' → ' . __('Invoice')
            : ($job ? __('Job') . ' → ' . __('Invoice') : __('Invoice created directly'));

        $logs = \App\Models\Log\LogHistory::where('loggable_type', CustomerInvoice::class)
            ->where('loggable_id', $inv->id)->where('created_at', '>=', $inv->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        $ignore = ['status', 'paid_amount', 'updated_at', 'posted_at', 'tax_submit_status', 'tax_submitted_at'];
        $seenCreate = false;
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seenCreate = true;
                $add(__('Invoice created') . ' · ' . $inv->row_no, $log->created_at, 'bi-receipt', $by);
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $label = \App\Enums\CustomerInvoiceEnum::tryFrom($st)?->label() ?? __('Status changed');
                    $icon = $st === \App\Enums\CustomerInvoiceEnum::APPROVED->value ? 'bi-check-circle'
                        : ($st === \App\Enums\CustomerInvoiceEnum::CANCELLED->value ? 'bi-x-circle' : 'bi-clock');
                    $add($label, $log->created_at, $icon, $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Invoice updated'), $log->created_at, 'bi-pencil-square', $by);
                }
            }
        }
        if (!$seenCreate) $add(__('Invoice created') . ' · ' . $inv->row_no, $inv->created_at, 'bi-receipt');

        $grand = (float) $inv->grand_total;
        $dec = decimals();
        foreach (\App\Models\Finance\Adjustment\CreditNote::where('invoice_id', $inv->id)->orderBy('id')->get() as $cn) {
            $kind = $grand > 0 && (float) $cn->grand_total >= $grand - 0.005 ? __('Full credit note') : __('Partial credit note');
            $add($kind . ' · ' . $cn->row_no, $cn->created_at, 'bi-receipt-cutoff', null, number_format((float) $cn->grand_total, $dec), 'credit_note', ['credit_note', $cn->id, $cn->row_no]);
        }

        $cumulative = 0.0;
        $cis = \App\Models\Finance\Collection\CollectionInvoice::where('customer_invoice_id', $inv->id)
            ->with('collection:id,row_no,collection_date,status,created_at')->orderBy('id')->get();
        foreach ($cis as $ci) {
            if (($ci->collection->status ?? 0) == 3) continue; // cancelled
            $cumulative += (float) $ci->amount;
            $kind = $grand > 0 && $cumulative >= $grand - 0.005 ? __('Full collection') : __('Partial collection');
            $add($kind . ' · ' . ($ci->collection->row_no ?? ''), $ci->collection->created_at ?? $ci->created_at, 'bi-cash-coin', null, number_format((float) $ci->amount, $dec), 'collection', $ci->collection_id ? ['collection', $ci->collection_id, $ci->collection->row_no ?? ''] : null);
        }

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }

    /**
     * Collections and credit notes recorded against this invoice, merged into
     * one chronological list so the invoice view can show both transaction
     * types side by side instead of just collections.
     */
    private function invoiceTransactions($id): \Illuminate\Support\Collection
    {
        $collectionStatusLabels = [1 => ['Draft', 'secondary'], 2 => ['Approved', 'success'], 3 => ['Cancelled', 'danger']];
        $creditNoteStatusLabels = [1 => ['Draft', 'secondary'], 2 => ['Approved', 'success'], 3 => ['Cancelled', 'danger']];

        $collectionInvoices = \App\Models\Finance\Collection\CollectionInvoice::where('customer_invoice_id', $id)
            ->with('collection:id,row_no,collection_date,account,currency,status,created_by,created_at,approved_by,approved_at')
            ->get();

        $creditNotes = \App\Models\Finance\Adjustment\CreditNote::where('invoice_id', $id)->get();

        // Who created / approved each one and when. Collections keep created_by, approved_by and approved_at;
        // credit notes keep user_id, and their approval is read from the change log (the status change to Approved).
        $cnApproval = [];
        if ($creditNotes->isNotEmpty()) {
            $logs = \App\Models\Log\LogHistory::where('loggable_type', \App\Models\Finance\Adjustment\CreditNote::class)
                ->whereIn('loggable_id', $creditNotes->pluck('id'))->where('action', 'updated')->orderBy('id')->get();
            foreach ($logs as $log) {
                if ((int) ($log->changes['new']['status'] ?? 0) === 2) {
                    $cnApproval[$log->loggable_id] = ['by' => $log->user_id['name'] ?? null, 'at' => $log->created_at];
                }
            }
        }
        $userIds = $collectionInvoices->flatMap(fn ($ci) => [$ci->collection->created_by ?? null, $ci->collection->approved_by ?? null])
            ->merge($creditNotes->pluck('user_id'))->filter()->unique()->values();
        $userNames = $userIds->isEmpty() ? collect() : \App\Models\User::withoutGlobalScopes()->whereIn('id', $userIds)->pluck('name', 'id');
        $fmt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y H:i') : null;
        // where each collection was paid into (its Paid Through account)
        $accountIds = $collectionInvoices->map(fn ($ci) => $ci->collection->account ?? null)->filter()->unique()->values();
        $accountNames = $accountIds->isEmpty() ? collect() : DB::table('accounts')->whereIn('id', $accountIds)->pluck('name', 'id');

        $transactions = collect();

        foreach ($collectionInvoices as $ci) {
            $label = $collectionStatusLabels[$ci->collection->status ?? 0] ?? ['Unknown', 'secondary'];
            $transactions->push([
                'type' => 'Collection',
                'kind' => 'collection',
                'id' => $ci->collection_id,
                'type_color' => 'primary',
                'reference' => $ci->collection->row_no ?? '—',
                'url' => $ci->collection_id ? url('transaction/collections/' . $ci->collection_id) : null,
                'date' => $ci->collection->collection_date ?? null,
                'amount' => $ci->amount,
                // what happened to the money
                'note' => ($accountNames[$ci->collection->account ?? 0] ?? null) ? __('Credited to') . ' ' . $accountNames[$ci->collection->account] : null,
                'note_color' => 'success',
                'by' => $userNames[$ci->collection->created_by ?? 0] ?? null,
                'created_on' => $fmt($ci->collection->created_at ?? $ci->collection->collection_date ?? null),
                'approved_by' => $userNames[$ci->collection->approved_by ?? 0] ?? null,
                'approved_on' => $fmt($ci->collection->approved_at ?? null),
                'status_label' => $label[0],
                'status_color' => $label[1],
            ]);
        }

        foreach ($creditNotes as $cn) {
            $label = $creditNoteStatusLabels[$cn->status] ?? ['Unknown', 'secondary'];
            $transactions->push([
                'type' => 'Credit Note',
                'kind' => 'credit_note',
                'id' => $cn->id,
                'type_color' => 'info',
                'reference' => $cn->row_no ?? '—',
                'url' => url('adjustment/credit-note/' . $cn->id . '/create'),
                'date' => $cn->created_at,
                'amount' => $cn->grand_total,
                // a credit note against an invoice reduces what this invoice still owes
                'note' => __('Applied to this invoice') . ($cn->reason ? ' · ' . \Illuminate\Support\Str::headline($cn->reason) : ''),
                'note_color' => 'info',
                'by' => $userNames[$cn->user_id ?? 0] ?? null,
                'created_on' => $fmt($cn->created_at),
                'approved_by' => $cnApproval[$cn->id]['by'] ?? null,
                'approved_on' => $fmt($cnApproval[$cn->id]['at'] ?? null),
                'status_label' => $label[0],
                'status_color' => $label[1],
            ]);
        }

        // Customer credit moved between invoices (on a collection): shown on both invoices
        $apps = DB::table('customer_credit_applications as ca')
            ->join('collections as c', 'c.id', '=', 'ca.collection_id')
            ->where('ca.status', 1)->where(fn ($q) => $q->where('ca.to_invoice_id', $id)->orWhere('ca.from_invoice_id', $id))
            ->get(['ca.*', 'c.row_no as col_no', 'c.created_by as col_created_by', 'c.created_at as col_created_at', 'c.approved_by as col_approved_by', 'c.approved_at as col_approved_at']);
        if ($apps->isNotEmpty()) {
            $invNos = DB::table('customer_invoices')->whereIn('id', $apps->flatMap(fn ($a) => [$a->from_invoice_id, $a->to_invoice_id])->unique())->pluck('row_no', 'id');
            $names = DB::table('users')->whereIn('id', $apps->flatMap(fn ($a) => [$a->col_created_by, $a->col_approved_by])->filter()->unique())->pluck('name', 'id');
            foreach ($apps as $a) {
                $incoming = (int) $a->to_invoice_id === (int) $id;
                $transactions->push([
                    'type' => $incoming ? 'Credit Applied' : 'Credit Moved',
                    'kind' => 'collection',
                    'id' => $a->collection_id,
                    'type_color' => 'info',
                    'reference' => $a->col_no,
                    'url' => null,
                    'date' => $a->col_approved_at ?: $a->col_created_at,
                    'amount' => $incoming ? $a->amount : -$a->amount,
                    'note' => $incoming
                        ? __('Customer credit from :no applied to this invoice', ['no' => $invNos[$a->from_invoice_id] ?? '#' . $a->from_invoice_id])
                        : __('Customer credit moved to :no', ['no' => $invNos[$a->to_invoice_id] ?? '#' . $a->to_invoice_id]),
                    'note_color' => 'info',
                    'by' => $names[$a->col_created_by] ?? null,
                    'created_on' => $fmt($a->col_created_at),
                    'approved_by' => $names[$a->col_approved_by] ?? null,
                    'approved_on' => $fmt($a->col_approved_at),
                    'status_label' => 'Approved',
                    'status_color' => 'success',
                ]);
            }
        }

        return $transactions->sortByDesc(fn($t) => $t['date'])->values();
    }

    /**
     * Collections (payments) recorded against this invoice so far — read-only,
     * pulled straight from the existing collection_invoices pivot rather than
     * duplicating any collection logic.
     */
    public function paymentHistory($id)
    {
        $customerInvoice = CustomerInvoice::findOrFail($id);

        $collectionInvoices = \App\Models\Finance\Collection\CollectionInvoice::where('customer_invoice_id', $id)
            ->with('collection:id,row_no,collection_date,account,currency,status,created_by,created_at')
            ->orderByDesc('id')
            ->get();

        // Who entered each collection (created_by, or the user who saved it) and when.
        $enteredBy = \App\Models\User::withoutGlobalScopes()
            ->whereIn('id', $collectionInvoices->map(fn($ci) => $ci->collection?->created_by)->filter()->unique())
            ->pluck('name', 'id');

        $balance = (float) $customerInvoice->grand_total - (float) ($customerInvoice->paid_amount ?? 0);

        return view('modules.finance.customer-invoice.payment-history', compact('customerInvoice', 'collectionInvoices', 'balance', 'enteredBy'));
    }

    public function print($id)
    {
        $customerInvoice = $this->allPrint($id);
        $settings = \App\Models\Settings\InvoiceSettings::invoiceSettings();

        [$template, $data] = $this->buildPrintViewData($customerInvoice, $settings);

        return view($template, $data);
    }

    /**
     * Render one of the 4 invoice themes against a real invoice using
     * NOT-YET-SAVED settings values, so the Invoice Settings page's preview
     * pane can show the actual template output as the user toggles options,
     * without persisting every keystroke.
     */
    public function previewTemplate(Request $request)
    {
        $customerInvoice = CustomerInvoice::with('customerInvoiceSubs', 'customer', 'job.activity', 'jobContainers', 'jobPackages')
            ->where('company_id', companyId())
            ->latest('id')
            ->first();

        if (!$customerInvoice) {
            return response('<div style="padding:40px;text-align:center;font-family:Arial,sans-serif;color:#888;">No invoice found yet to preview against &mdash; create a customer invoice first.</div>');
        }

        $settings = new \App\Models\Settings\InvoiceSettings($request->only([
            'theme', 'primary_color', 'party_balance', 'free_item_qty', 'item_description', 'alt_unit',
            'show_phone', 'show_time', 'awb_hbl', 'incoterm', 'pol_pod', 'voyage_flight', 'shipment_mode',
            'carrier', 'hsn_sac', 'unit', 'rate', 'discount', 'currency',
        ]));
        $settings->custom_fields = json_encode([
            'invoice_details' => $request->input('invoice_details', []),
            'party_details' => $request->input('party_details', []),
            'misc_details' => $request->input('misc_details', []),
        ]);

        [$template, $data] = $this->buildPrintViewData($customerInvoice, $settings);

        return view($template, $data);
    }

    /**
     * Every optional block/column in the templates is always rendered into the
     * DOM, tagged with data-toggle="<key>", and hidden via inline
     * style="display:none" when its setting is off — `display:none` elements
     * are skipped by both the browser's native print and html2pdf, so this is
     * safe for the real invoice output. It also means the Invoice Settings page
     * can flip checkboxes purely client-side (toggle that inline style on the
     * already-rendered preview iframe) with zero extra server requests.
     */
    private function buildPrintViewData(CustomerInvoice $customerInvoice, \App\Models\Settings\InvoiceSettings $settings): array
    {
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        $bank = Bank::orderBy('sort')->first();
        $company = authUserCompany();
        $jobContainers = $customerInvoice->jobContainers->map(fn ($c) => [
            'container_size' => $c->container_size,
            'qty' => $c->qty,
            'container_number' => $c->container_number,
        ]);
        $jobPackages = $customerInvoice->jobPackages ?? [];

        $customFields = json_decode($settings->custom_fields ?? '[]', true) ?: [];
        $invoiceDetailToggles = $customFields['invoice_details'] ?? [];
        $partyDetailToggles = $customFields['party_details'] ?? [];

        $job = $customerInvoice->job;
        $customer = $customerInvoice->customer;

        // Customer's overall outstanding balance across approved invoices
        // (same definition/query shape as CustomerAging report, for consistency).
        $customerBalance = 0.0;
        if ($customer) {
            $customerBalance = (float) CustomerInvoice::where('customer_id', $customer->id)
                ->where('status', 3)
                ->whereRaw('COALESCE(paid_amount, 0) < grand_total')
                ->get()
                ->sum(fn ($inv) => (float) $inv->grand_total - (float) ($inv->paid_amount ?? 0));
        }

        // Extra job-detail rows, only the ones toggled on AND backed by a real column.
        $extraJobFields = collect([
            ['key' => 'job_number', 'label_en' => 'Job Number', 'label_ar' => 'رقم الوظيفة', 'value' => $job->row_no ?? null],
            ['key' => 'reference_number', 'label_en' => 'Reference Number', 'label_ar' => 'الرقم المرجعي', 'value' => $job->client_ref ?? null],
            ['key' => 'activity', 'label_en' => 'Logistics Activity', 'label_ar' => 'النشاط اللوجستي', 'value' => optional($job?->activity)->name],
            ['key' => 'shipment_category', 'label_en' => 'Shipment Category', 'label_ar' => 'فئة الشحنة', 'value' => $job->shipment_category ?? null],
            ['key' => 'place_of_receipt', 'label_en' => 'Place of Receipt', 'label_ar' => 'مكان الاستلام', 'value' => $job->place_of_receipt ?? null],
            ['key' => 'place_of_delivery', 'label_en' => 'Place of Delivery', 'label_ar' => 'مكان التسليم', 'value' => $job->place_of_delivery ?? null],
            ['key' => 'final_destination', 'label_en' => 'Final Destination', 'label_ar' => 'الوجهة النهائية', 'value' => $job->final_destination ?? null],
            ['key' => 'commodity', 'label_en' => 'Commodity', 'label_ar' => 'البضاعة', 'value' => $job->commodity ?? null],
            ['key' => 'pickup_date', 'label_en' => 'Pickup Date', 'label_ar' => 'تاريخ الاستلام', 'value' => $job?->pickup_date ? showDate($job->pickup_date) : null],
            ['key' => 'delivery_date', 'label_en' => 'Delivery Date', 'label_ar' => 'تاريخ التسليم', 'value' => $job?->delivery_date ? showDate($job->delivery_date) : null],
            ['key' => 'eta', 'label_en' => 'ETA', 'label_ar' => 'التاريخ المتوقع للوصول', 'value' => $job?->eta ? showDate($job->eta) : null],
            ['key' => 'etd', 'label_en' => 'ETD', 'label_ar' => 'التاريخ المتوقع للمغادرة', 'value' => $job?->etd ? showDate($job->etd) : null],
        ])->map(fn ($f) => $f + ['visible' => $invoiceDetailToggles[$f['key']] ?? false])
          ->values();

        // The shipment rows (AWB/HBL, origin / destination, carrier, voyage, incoterm, mode) are fixed rows in the Stylish /
        // Luxury / Advanced / Billbook layouts; the other themes show them through this list, switched by the same settings.
        $shipmentFields = collect([
            ['key' => 'awb_hbl', 'label_en' => 'AWB / HBL No', 'label_ar' => 'رقم بوليصة الشحن الفرعية', 'value' => collect([$job?->awb_number, $job?->hbl_number])->filter()->implode(' / ')],
            ['key' => 'pol_pod', 'label_en' => 'Place of Origin', 'label_ar' => 'بلد المنشأ', 'value' => $job?->pol],
            ['key' => 'pol_pod', 'label_en' => 'Final Destination', 'label_ar' => 'الوجهة النهائية', 'value' => $job?->pod],
            ['key' => 'carrier', 'label_en' => 'Vessel / Flight', 'label_ar' => 'الناقل / الرحلة', 'value' => $job?->carrier],
            ['key' => 'voyage_flight', 'label_en' => 'Voyage / Flight No.', 'label_ar' => 'رقم الرحلة', 'value' => $job?->voyage_flight_no],
            ['key' => 'incoterm', 'label_en' => 'Incoterm', 'label_ar' => 'شروط التسليم', 'value' => $job?->incoterm],
            ['key' => 'shipment_mode', 'label_en' => 'Shipment Mode', 'label_ar' => 'وسيلة الشحن', 'value' => $job?->shipment_mode],
        ])->map(fn ($f) => $f + ['visible' => (bool) ($settings->{$f['key']} ?? false)]);
        $extraJobFields = $extraJobFields->concat($shipmentFields)->values();

        // Extra customer-detail rows, same rule.
        $extraPartyFields = collect([
            ['key' => 'code', 'label_en' => 'Customer Code', 'label_ar' => 'رمز العميل', 'value' => $customer->row_no ?? null],
            ['key' => 'unique_code', 'label_en' => 'Unique Code', 'label_ar' => 'الرمز الفريد', 'value' => $customer->unique_row_no ?? null],
            ['key' => 'business_type', 'label_en' => 'Business Type', 'label_ar' => 'نوع النشاط', 'value' => $customer->business_type ?? null],
            ['key' => 'cr_number', 'label_en' => 'CR Number', 'label_ar' => 'رقم السجل التجاري', 'value' => $customer->cr_number ?? null],
            ['key' => 'vat_number', 'label_en' => 'VAT Number', 'label_ar' => 'الرقم الضريبي', 'value' => $customer->vat_number ?? null],
            ['key' => 'credit_limit', 'label_en' => 'Credit Limit', 'label_ar' => 'حد الائتمان', 'value' => $customer?->credit_limit ? amountFormat($customer->credit_limit) : null],
            ['key' => 'credit_days', 'label_en' => 'Credit Days', 'label_ar' => 'أيام الائتمان', 'value' => $customer->credit_days ?? null],
            ['key' => 'region', 'label_en' => 'Region', 'label_ar' => 'المنطقة', 'value' => $customer->city_en ?? $customer->city ?? null],
            ['key' => 'postal_code', 'label_en' => 'Postal Code', 'label_ar' => 'الرمز البريدي', 'value' => $customer->postal_code ?? null],
            ['key' => 'country', 'label_en' => 'Country', 'label_ar' => 'الدولة', 'value' => $customer->country ?? null],
            ['key' => 'email', 'label_en' => 'Email', 'label_ar' => 'البريد الإلكتروني', 'value' => $customer->email ?? null],
            ['key' => 'alt_phone', 'label_en' => 'Alt. Phone', 'label_ar' => 'هاتف بديل', 'value' => $customer->alt_phone ?? null],
            ['key' => 'preferred_shipping', 'label_en' => 'Preferred Shipping', 'label_ar' => 'الشحن المفضل', 'value' => $customer->preferred_shipping ?? null],
            ['key' => 'preferred_carrier', 'label_en' => 'Preferred Carrier', 'label_ar' => 'الناقل المفضل', 'value' => $customer->preferred_carrier ?? null],
            ['key' => 'default_port', 'label_en' => 'Default Port', 'label_ar' => 'الميناء الافتراضي', 'value' => $customer->default_port ?? null],
            ['key' => 'payment_method', 'label_en' => 'Payment Method', 'label_ar' => 'طريقة الدفع', 'value' => $customer->payment_method ?? null],
            ['key' => 'iban', 'label_en' => 'IBAN', 'label_ar' => 'الآيبان', 'value' => $customer->iban ?? null],
            ['key' => 'payment_terms', 'label_en' => 'Payment Terms', 'label_ar' => 'شروط الدفع', 'value' => $customer->payment_terms ?? null],
            ['key' => 'salesperson', 'label_en' => 'Salesperson', 'label_ar' => 'مندوب المبيعات', 'value' => optional($customer?->salesperson)->name],
        ])->map(fn ($f) => $f + ['visible' => $partyDetailToggles[$f['key']] ?? false])
          ->values();

        $themeTemplates = [
            'stylish' => 'modules.print.format-2.print',
            'advance-gst-tally' => 'modules.print.format-4.print',
            'bilingual' => 'modules.print.format-6.print',
            'fastfatoora' => 'modules.print.format-7.print',
            'ai-fatoora' => 'modules.print.format-8.print',
            'fastfatoora-classic' => 'modules.print.format-9.print',
        ];

        // Each cloned theme ships its own brand colour. A company only overrides it
        // by deliberately picking a different swatch; the untouched app default
        // ($appDefaultColor) is treated as "no choice" so the clone stays faithful.
        $appDefaultColor = '#16a34a';
        $legacyDefaultColors = ['#0b6aa0', '#2563eb'];
        $themeAccents = [
            'bilingual' => '#2FA36B',
            'fastfatoora' => '#15803d',
            'ai-fatoora' => '#0f2a52',
            'fastfatoora-classic' => '#111827',
        ];
        $chosen = $settings->primary_color ?: null;
        $isDefaultChoice = !$chosen || strcasecmp($chosen, $appDefaultColor) === 0
            || in_array(strtolower($chosen), $legacyDefaultColors, true);
        $accentColor = (!$isDefaultChoice)
            ? $chosen
            : ($themeAccents[$settings->theme] ?? $appDefaultColor);

        $template = $themeTemplates[$settings->theme] ?? $themeTemplates['stylish'];

        // The due date is stored in due_at; the print templates read due_date.
        if (empty($customerInvoice->due_date) && !empty($customerInvoice->due_at)) {
            $customerInvoice->due_date = $customerInvoice->due_at;
        }

        return [$template, compact(
            'customerInvoice', 'descriptions', 'bank', 'company', 'jobContainers', 'jobPackages',
            'settings', 'customerBalance', 'extraJobFields', 'extraPartyFields', 'accentColor'
        )];
    }

    public function allPrint($id)
    {
        return CustomerInvoice::with('customerInvoiceSubs', 'customer', 'job', 'jobContainers', 'jobPackages')->findOrFail($id);
    }

    /**
     * Create finance entries for a customer invoice.
     *
     * @param CustomerInvoice $customerInvoice
     * @return void
     */
    private function createCustomerInvoiceFinanceEntries(CustomerInvoice $customerInvoice)
    {
        DB::beginTransaction();

        try {
            $this->deleteCustomerInvoiceFinanceEntries($customerInvoice->id);

            $finance = new Finance();
            $finance->voucher_no = $customerInvoice->row_no;
            $finance->voucher_type = 'CI';
            $finance->reference_no = $customerInvoice->invoice_number ?? $customerInvoice->row_no;
            $finance->reference_date = formDate($customerInvoice->invoice_date);
            $finance->customer_id = $customerInvoice->customer_id;
            $finance->narration = 'Customer Invoice: ' . $customerInvoice->row_no;
            $finance->currency = $customerInvoice->currency ?? 'SAR';
            $finance->exchange_rate = $customerInvoice->currency_rate ?? 1;

            $finance->total_debit = $customerInvoice->grand_total;
            $finance->total_credit = $customerInvoice->grand_total;
            $finance->base_total_debit = $customerInvoice->base_grand_total;
            $finance->base_total_credit = $customerInvoice->base_grand_total;

            $finance->job_id = $customerInvoice->job_id;
            $finance->job_no = $customerInvoice->job_no ?? '';
            $finance->is_approved = 1;
            $finance->posted_at = now();
            $finance->linked_id = $customerInvoice->id;
            $finance->linked_type = CustomerInvoice::class;
            $finance->company_id = $customerInvoice->company_id;
            $finance->user_id = Auth::id();
            $finance->save();

            $financeSubs = [];

            // IMPORTANT: Every key defined here MUST exist in every sub-array
            $commonData = [
                'finance_id' => $finance->id,
                'voucher_no' => $finance->voucher_no,
                'voucher_type' => $finance->voucher_type,
                'reference_no' => $finance->reference_no,
                'reference_date' => formDate($customerInvoice->invoice_date),
                'currency' => $finance->currency,
                'exchange_rate' => $finance->exchange_rate,
                'base_currency' => 'SAR',
                'customer_id' => $customerInvoice->customer_id,
                'job_id' => $customerInvoice->job_id,
                'job_no' => $customerInvoice->job_no,
                'company_id' => $customerInvoice->company_id,
                'user_id' => Auth::id(),
                'is_tax_line' => 0, // Set a default so all rows have this column
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // LINE 1: Accounts Receivable (DEBIT)
            $financeSubs[] = array_merge($commonData, [
                'account_id' => 5,//1130
                'description' => 'Receivable - Invoice ' . $customerInvoice->row_no,
                'debit' => $customerInvoice->grand_total,
                'credit' => 0,
                'base_debit' => $customerInvoice->base_grand_total,
                'base_credit' => 0,
            ]);

            // LINE 2: Revenue (CREDIT) — one line per income account chosen on the invoice lines
            // (falls back to Sales Revenue 4160 for lines without an account), so income reports split correctly.
            $revenueByAccount = [];
            foreach ($customerInvoice->customerInvoiceSubs as $sub) {
                $accId = $sub->account_id ?: 38; // 4160 Sales Revenue
                $revenueByAccount[$accId]['total'] = ($revenueByAccount[$accId]['total'] ?? 0) + (float) $sub->total;
                $revenueByAccount[$accId]['base'] = ($revenueByAccount[$accId]['base'] ?? 0) + (float) $sub->base_total;
            }
            if (!$revenueByAccount) {
                $revenueByAccount[38] = ['total' => (float) $customerInvoice->sub_total, 'base' => (float) $customerInvoice->base_sub_total];
            }
            foreach ($revenueByAccount as $accId => $amt) {
                $financeSubs[] = array_merge($commonData, [
                    'account_id' => $accId,
                    'description' => 'Sales Revenue - Invoice ' . $customerInvoice->row_no,
                    'debit' => 0,
                    'credit' => round($amt['total'], 2),
                    'base_debit' => 0,
                    'base_credit' => round($amt['base'], 2),
                ]);
            }

            // LINE 3: VAT Payable (CREDIT)
            if ($customerInvoice->tax_total > 0) {
                $financeSubs[] = array_merge($commonData, [
                    'account_id' => 20,//2130
                    'description' => 'Output VAT - Invoice ' . $customerInvoice->row_no,
                    'debit' => 0,
                    'credit' => $customerInvoice->tax_total,
                    'base_debit' => 0,
                    'base_credit' => $customerInvoice->base_tax_total,
                    'is_tax_line' => 1, // Override the default 0
                ]);
            }

            FinanceSub::insert($financeSubs);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finance Entry Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete finance entries for a customer invoice.
     *
     * @param int $customerInvoiceId
     * @return void
     */
    private function deleteCustomerInvoiceFinanceEntries($customerInvoiceId)
    {
        try {
            // Find all finance entries linked to this customer invoice
            $financeEntries = Finance::where('linked_id', $customerInvoiceId)
                ->where('linked_type', CustomerInvoice::class)
                ->get();

            // Delete each finance entry and its sub entries
            foreach ($financeEntries as $finance) {
                // Delete finance sub entries first
                FinanceSub::where('finance_id', $finance->id)->delete();

                // Then delete the finance entry
                $finance->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error deleting finance entries for customer invoice: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Everything on a job that a customer invoice can be built from:
     *  - its quotation(s): the SELLING lines (one per job normally)  -> provisional revenue
     *  - its supplier invoices: the COST lines (several are normal)  -> actual cost, re-billed with a margin
     * Each entry also says which customer invoice already used it, so the same cost is not billed twice by mistake.
     * Revenue account = the description's sale account, falling back to Freight Income.
     * ?invoice=ID leaves that invoice out of the "already billed" list (editing it).
     */
    public function jobCosts(Request $request, $jobId)
    {
        $exclude = (int) $request->get('invoice');
        $bills = \App\Models\Finance\SupplierInvoice\SupplierInvoice::where('job_id', $jobId)
            ->where('status', \App\Enums\SupplierInvoiceEnum::APPROVED->value)
            ->with(['supplier:id,name_en', 'supplierInvoiceSubs'])
            ->orderBy('id')->get();
        $quotes = \App\Models\Quotation\Quotation::where('job_id', $jobId)
            ->whereIn('status', [\App\Enums\QuotationEnum::ACCEPTED->value, \App\Enums\QuotationEnum::CONVERTED->value])   // approved only
            ->orderBy('id')->get(['id', 'row_no', 'quotation_no', 'currency', 'currency_rate', 'grand_total', 'status']);
        $charges = DB::table('quotation_charges')->whereIn('quotation_id', $quotes->pluck('id'))->orderBy('sort_order')->orderBy('line_no')->get()->groupBy('quotation_id');

        // which customer invoices already used these (cancelled ones do not count)
        $used = fn (string $type, $ids) => DB::table('customer_invoice_subs as s')
            ->join('customer_invoices as i', 'i.id', '=', 's.customer_invoice_id')
            ->where('s.source_type', $type)->whereIn('s.source_id', $ids)
            ->whereNull('s.deleted_at')->whereNull('i.deleted_at')
            ->where('i.status', '!=', CustomerInvoiceEnum::CANCELLED->value)
            ->when($exclude, fn ($q) => $q->where('i.id', '!=', $exclude))
            ->select('s.source_id', 'i.row_no')->distinct()->get()->groupBy('source_id');
        $usedBills = $used('supplier_invoice', $bills->pluck('id'));
        $usedQuotes = $used('quotation', $quotes->pluck('id'));

        $allDesc = DB::table('descriptions')->get(['id', 'description', 'sale_account_id']);
        $descById = $allDesc->keyBy('id');
        $descByName = $allDesc->keyBy(fn ($d) => mb_strtolower(trim($d->description)));
        $units = DB::table('units')->get(['id', 'unit_name', 'unit_symbol']);
        $unitByName = [];
        foreach ($units as $u) {
            $unitByName[mb_strtolower(trim($u->unit_name))] = $u->id;
            $unitByName[mb_strtolower(trim((string) $u->unit_symbol))] = $u->id;
        }
        // quotation charges carry legacy ZATCA letters; map them to this app's tax codes
        $taxMap = ['S' => 'STANDARD', 'Z' => 'ZERO', 'E' => 'EXEMPT', 'O' => 'OUTOFSCOPE'];

        return response()->json([
            'quotations' => $quotes->map(function ($q) use ($charges, $descByName, $allDesc, $unitByName, $taxMap, $usedQuotes) {
                return [
                    'id' => $q->id,
                    'row_no' => $q->row_no ?: $q->quotation_no,
                    'currency' => $q->currency,
                    'total' => round((float) $charges->get($q->id, collect())->sum('local_amount'), 2),
                    'billed_in' => ($usedQuotes[$q->id] ?? collect())->pluck('row_no')->values(),
                    'lines' => $charges->get($q->id, collect())->map(function ($c) use ($descByName, $allDesc, $unitByName, $taxMap) {
                        $name = mb_strtolower(trim((string) $c->charge_description));
                        // exact name first, then a description that contains it (or is contained by it)
                        $d = $descByName[$name] ?? $allDesc->first(fn ($x) => $name !== '' && (str_contains(mb_strtolower($x->description), $name) || str_contains($name, mb_strtolower(trim($x->description)))));
                        $qty = (float) $c->qty ?: 1;
                        return [
                            'source_sub_id' => $c->id,
                            'description_id' => $d->id ?? null,
                            'description_text' => $c->charge_description,
                            'comment' => $c->remarks ?: ($d ? null : $c->charge_description),
                            'unit_id' => $unitByName[mb_strtolower(trim((string) $c->unit))] ?? null,
                            'quantity' => $qty,
                            // price per unit in company currency; the form divides by the invoice's own rate
                            'unit_price_local' => round((float) $c->local_amount / $qty, 4),
                            'tax_code' => $taxMap[$c->tax_group_code] ?? ($c->tax_group_code ?: 'STANDARD'),
                            'account_id' => ($d->sale_account_id ?? null) ?: 32,
                        ];
                    })->values(),
                ];
            })->values(),
            'bills' => $bills->map(function ($b) use ($descById, $usedBills) {
                return [
                    'id' => $b->id,
                    'row_no' => $b->row_no,
                    'invoice_number' => $b->invoice_number,
                    'supplier' => $b->supplier?->name_en,
                    'status' => (int) ($b->status instanceof \BackedEnum ? $b->status->value : $b->status),
                    'grand_total' => round((float) $b->grand_total, 2),
                    'currency' => $b->currency,
                    'billed_in' => ($usedBills[$b->id] ?? collect())->pluck('row_no')->values(),
                    'lines' => $b->supplierInvoiceSubs->map(fn ($l) => [
                        'source_sub_id' => $l->id,
                        'description_id' => $l->description_id,
                        'comment' => $l->comment,
                        'unit_id' => $l->unit_id,
                        'quantity' => (float) $l->quantity,
                        'unit_price' => round((float) $l->unit_price, 2),
                        // the cost behind the line in company currency, excl. VAT
                        'cost_base' => round((float) $l->base_total, 4),
                        'tax_code' => $l->tax_code,
                        'account_id' => ((($descById[$l->description_id]->sale_account_id ?? null))) ?: 32,
                    ])->values(),
                ];
            })->values(),
        ]);
    }

    /** Delete is allowed only when nothing else points at this record (see DeletionGuard). */
    public function delete($id)
    {
        $model = CustomerInvoice::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('customer_invoice', (int) $id);
        if ($why) {
            return $guard->refusal(__('invoice'), $why);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($model, $id) {
            app(\App\Services\Finance\SupplierInvoiceLinks::class)->release((int) $id);   // frees the supplier invoices it billed
            \Illuminate\Support\Facades\DB::table('customer_invoice_subs')->where('customer_invoice_id', $id)->delete();
            $model->delete();
        });

        return response()->json(['status' => 'success', 'message' => __('Invoice deleted successfully')]);
    }
}
