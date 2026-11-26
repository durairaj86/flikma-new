<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\SupplierStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\FinanceSub;
use App\Models\Supplier\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        // Outstanding balance across this supplier's approved, unsettled invoices,
        // plus how many of those are overdue — shown as the "Due" column. Uses a
        // raw subquery (not the SupplierInvoice model), so company scoping is
        // applied explicitly here instead of relying on the model's global scope.
        $dueInvoiceQuery = function ($query) {
            $query->from('supplier_invoices')
                ->whereColumn('supplier_invoices.supplier_id', 'suppliers.id')
                ->where('status', \App\Enums\SupplierInvoiceEnum::APPROVED->value)
                ->whereColumn('paid_amount', '<', 'grand_total')
                ->where('company_id', companyId());
        };

        $rows = Supplier::select(
            'id',
            'name_en',
            'row_no',
            'email',
            'contact_person',
            'currency',
            'business_type',
            'city_en',
            'city_ar',
            'country',
            'phone',
            'vat_number',
            'credit_limit',
            'credit_days',
            'created_at',
            'company_id'
        )
            ->selectSub(function ($query) use ($dueInvoiceQuery) {
                $dueInvoiceQuery($query);
                // unpaid approved bills + what is still owed on the opening balance
                $query->selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) + ' . \App\Services\Finance\OpeningSettlement::outstandingSql('supplier', 'suppliers'));
            }, 'due_amount')
            ->selectSub(function ($query) use ($dueInvoiceQuery) {
                $dueInvoiceQuery($query);
                $query->where('due_at', '<', now())->selectRaw('COUNT(*)');
            }, 'overdue_count')
            // advance paid to the supplier (excess over the ticked bills), less what was used on bills or refunded back
            ->selectSub(function ($query) {
                $query->from('payments as p')->whereColumn('p.supplier_id', 'suppliers.id')->whereNull('p.deleted_at')
                    ->where('p.company_id', companyId())->where('p.status', \App\Enums\PaymentEnum::APPROVED->value)
                    ->selectRaw('GREATEST(COALESCE(SUM(p.advance_amount - p.advance_used), 0), 0)');
            }, 'advance_held')
            ->orderBy('name_en');

        if (strtolower((string) $request->tab) === 'overdue') {
            // Active suppliers with at least one approved, unpaid bill past its due date.
            $rows->where('status', SupplierStatusEnum::CONFIRMED->value)
                ->whereExists(function ($query) use ($dueInvoiceQuery) {
                    $dueInvoiceQuery($query);
                    $query->where('due_at', '<', now())->selectRaw('1');
                });
        } else {
            $rows->where('status', SupplierStatusEnum::fromName($request->tab));
        }

        if ($request->filled('joined_from')) {
            $rows->whereDate('created_at', '>=', formDate($request->joined_from));
        }
        if ($request->filled('joined_to')) {
            $rows->whereDate('created_at', '<=', formDate($request->joined_to));
        }

        // Get counts per status in one query
        $statusCounts = Supplier::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses (so missing ones appear as 0)
        $allCounts = [];
        foreach (SupplierStatusEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }

        $allCounts['overdue'] = Supplier::where('status', SupplierStatusEnum::CONFIRMED->value)
            ->whereExists(function ($query) use ($dueInvoiceQuery) {
                $dueInvoiceQuery($query);
                $query->where('due_at', '<', now())->selectRaw('1');
            })->count();

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            // due_amount / overdue_count are computed subselect aliases, not real columns,
            // so they can't appear in the search WHERE clause.
            ->filterColumn('due_amount', fn() => null)
            ->filterColumn('overdue_count', fn() => null)
            ->filterColumn('advance_held', fn() => null)
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => htmlspecialchars($model->name_en, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'supplier-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('business_type', function ($model) {
                return $model->business_type == 'registered'
                    ? ('Registered <br>' . $model->vat_number)
                    : 'Un-registered';
            })
            ->editColumn('created_at', fn($model) => Carbon::parse($model->created_at)->format('d-m-Y'))
            ->rawColumns(['business_type'])
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $supplier = new Supplier();
        return view('modules.supplier.supplier-form')->with('supplier', $supplier);
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('modules.supplier.supplier-form')->with('supplier', $supplier);
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'currency' => 'required|string|max:10',

            // Business type validation
            'business_type' => 'required|in:registered,unregistered',
            'cr_number' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:50',

            // Credit settings
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|numeric|min:0',

            // Address fields
            'address1_en' => 'nullable|string|max:255',
            'address1_ar' => 'nullable|string|max:255',
            'address2_en' => 'nullable|string|max:255',
            'address2_ar' => 'nullable|string|max:255',
            'city_en' => 'nullable|string|max:100',
            'city_ar' => 'nullable|string|max:100',
            'building_number' => 'nullable|max:10',
            'plot_no' => 'nullable|max:10',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',

            // Contact
            'contact_person' => 'nullable|string|max:100',
            // optional for suppliers, but without a rule the typed email was dropped on save
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:16|regex:/^\+?[0-9]{10,15}$/',
            'alt_phone' => 'nullable|min:10|max:16',
        ];

        // Conditional validation for registered business
        if ($request->business_type === 'registered') {
            $rules['cr_number'] = 'required|string|max:50';
            $rules['vat_number'] = 'required|string|max:50';
        }

        // Validate request
        $validated = $request->validate($rules);

        if (isset($request['data-id']) and filled($request['data-id'])) {
            $supplier = Supplier::findOrFail($request->input('data-id'));

            // Once a finance entry references this supplier, its currency is
            // locked — changing it after the fact would leave already-posted
            // invoices/payments in a currency that no longer matches the
            // supplier record, silently corrupting statements and reports.
            if ($supplier->currency !== $validated['currency']
                && FinanceSub::where('supplier_id', $supplier->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This supplier\'s currency cannot be changed because financial transactions already exist for them.',
                ], 422);
            }
        } else {
            $supplier = new Supplier();
            $supplier->unique_row_no = sprintf("%03d", (Supplier::max('unique_row_no') ?? 26000) + 1);
            $supplier->row_no = 'SP' . $supplier->unique_row_no;

            $this->setBaseColumns($supplier);
        }

        $supplier->name_en = $validated['name_en'];
        $supplier->name_ar = $validated['name_ar'];
        $supplier->currency = $validated['currency'];
        $supplier->business_type = $validated['business_type'];
        $supplier->cr_number = $validated['cr_number'] ?? null;
        $supplier->vat_number = $validated['vat_number'] ?? null;
        $supplier->credit_limit = $validated['credit_limit'] ?? null;
        $supplier->credit_days = $validated['credit_days'] ?? null;
        $supplier->address1_en = $validated['address1_en'] ?? null;
        $supplier->address1_ar = $validated['address1_ar'] ?? null;
        $supplier->city_en = $validated['city_en'] ?? null;
        $supplier->city_ar = $validated['city_ar'] ?? null;
        $supplier->building_number = $validated['building_number'] ?? null;
        $supplier->plot_no = $validated['plot_no'] ?? null;
        $supplier->postal_code = $validated['postal_code'] ?? null;
        $supplier->country = $validated['country'];
        $supplier->contact_person = $validated['contact_person'] ?? null;
        $supplier->email = $validated['email'] ?? null;
        $supplier->phone = $validated['phone'] ?? null;
        $supplier->alt_phone = $validated['alt_phone'] ?? null;
        $supplier->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Supplier created successfully'),
            'customer_id' => $supplier->id,
        ]);
    }

    public function updateStatus($id, $status)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->status = $status; // e.g., confirmed = 2
        $supplier->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Supplier status updated successfully!'),
            'data' => [
                'id' => $supplier->id,
                'status' => $supplier->status, // numeric (0,1,2..)
                'label' => SupplierStatusEnum::from($supplier->status)->label(), // "Confirmed"
                'color' => SupplierStatusEnum::from($supplier->status)->color(),
            ],
        ]);
    }

    public function actions($id)
    {
        $supplier = Supplier::select('id', 'row_no', 'name_en', 'name_ar', 'status')->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        $contextMenu->push([
            'label' => __('Statement'),
            'code' => '01INLI',
            'id' => 'row_statement',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'statement',
        ]);
        $contextMenu->push([
            'label' => __('Invoices'),
            'code' => '01INLI',
            'id' => 'row_search',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'search',
        ]);

        if ($supplier->status === SupplierStatusEnum::fromName('confirmed')) {
            $contextMenu->push([
                'label' => __('Block Supplier'),
                'code' => '01CSBK',
                'id' => 'row_blocked',
                'class' => 'row_blocked',
                'data-id' => $supplier->id,
                'data-value' => SupplierStatusEnum::fromName('blocked'),
                'type' => 'item',
                'icon' => 'blocked',
                'separator' => 'before',
            ]);
        } elseif ($supplier->status === SupplierStatusEnum::fromName('blocked')) {
            $contextMenu->push([
                'label' => __('Unblock Supplier'),
                'code' => '01CSCN',
                'id' => 'row_confirm',
                'class' => 'row_confirm',
                'data-id' => $supplier->id,
                'data-value' => SupplierStatusEnum::fromName('confirmed'),
                'type' => 'item',
                'icon' => 'confirmed',
                'separator' => 'before',
            ]);
        }
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('Actions'),
            'type' => 'submenu',
            'icon' => 'action',
            'items' => [
                [
                    'label' => __('Edit'),
                    'code' => '01CSED',
                    'id' => 'row_edit',
                    'class' => 'row_edit',
                    'data-id' => $supplier->id,
                    'type' => 'item',
                    'icon' => 'edit'
                ],
                [
                    'label' => __('Delete'),
                    'code' => '01CSDL',
                    'id' => 'row_delete',
                    'class' => 'row_delete',
                    'data-id' => $supplier->id,
                    'type' => 'item',
                    'icon' => 'delete'
                ]
            ]
        ]);

        return response()->json($contextMenu->values());
    }

    public function overview($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('modules.supplier.view-overview', compact('supplier'));
    }

    /** Delete is allowed only when nothing else points at this record (see DeletionGuard). */
    public function delete($id)
    {
        $model = Supplier::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('supplier', (int) $id);
        if ($why) {
            return $guard->refusal(__('supplier'), $why);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($model, $id) {
            $model->delete();
        });

        return response()->json(['status' => 'success', 'message' => __('Supplier deleted successfully')]);
    }
}
