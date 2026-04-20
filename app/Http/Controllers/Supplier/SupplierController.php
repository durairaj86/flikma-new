<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\SupplierStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Supplier\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Import\SupplierImport;

class SupplierController extends Controller
{
    /**
     * Import suppliers from Excel file - Step 1: Upload file and get columns
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importUpload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        // Store the file temporarily
        $file = $request->file('excelFile');

        // Ensure the temp directory exists
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $path = $file->storeAs('temp', $file->getClientOriginalName());

        // Store the file path in the session for later use
        session(['import_file_path' => $path]);

        // Use SupplierImport to get column headers and available fields
        $supplierImport = new SupplierImport();
        $headers = $supplierImport->getColumnHeaders($path);
        $fields = $supplierImport->getAvailableFields();

        return response()->json([
            'columns' => $headers,
            'fields' => $fields,
        ]);
    }

    /**
     * Import suppliers from Excel file - Step 2: Process the import
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importProcess(Request $request)
    {
        // Get the file path from the session
        $path = session('import_file_path');
        if (!$path) {
            return response()->json(['message' => 'No file uploaded. Please upload a file first.'], 400);
        }

        // No need to validate specific mapping fields as we'll check for non-empty values later

        // Get the mapping from the request (field => columnIndex)
        $requestMapping = $request->except(['_token']);

        // Transform the mapping to columnIndex => field format
        $mapping = [];
        foreach ($requestMapping as $field => $columnIndex) {
            if (filled($columnIndex)) {
                $mapping[$field] = $columnIndex;
            }
        }

        // Use SupplierImport to process the import
        $supplierImport = new SupplierImport();
        $result = $supplierImport->process($path, $mapping);

        // Delete the temporary file
        Storage::delete($path);
        session()->forget('import_file_path');

        // Return the result
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'imported' => $result['imported'] ?? 0,
                'errors' => $result['errors'] ?? [],
            ], isset($result['errors']) ? 422 : 400);
        }

        return response()->json([
            'message' => $result['message'],
            'imported' => $result['imported'],
        ]);
    }
    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $rows = Supplier::select(
            'id',
            'name_en',
            'row_no',
            'email',
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
            ->where('status', SupplierStatusEnum::fromName($request->tab))
            ->orderBy('name_en');

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

        return DataTables::eloquent($rows)
            ->addIndexColumn()
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
            //'email' => 'required|email|max:255',
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
        $supplier->cr_number = $validated['cr_number'];
        $supplier->vat_number = $validated['vat_number'];
        $supplier->credit_limit = $validated['credit_limit'];
        $supplier->credit_days = $validated['credit_days'];
        $supplier->address1_en = $validated['address1_en'];
        $supplier->address1_ar = $validated['address1_ar'];
        $supplier->city_en = $validated['city_en'];
        $supplier->city_ar = $validated['city_ar'];
        $supplier->building_number = $validated['building_number'];
        $supplier->plot_no = $validated['plot_no'];
        $supplier->postal_code = $validated['postal_code'];
        $supplier->country = $validated['country'];
        $supplier->email = $validated['email'] ?? null;
        $supplier->phone = $validated['phone'] ?? null;
        $supplier->alt_phone = $validated['alt_phone'];
        $supplier->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier created successfully',
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
            'message' => 'Supplier status updated successfully!',
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
        if ($supplier->status === SupplierStatusEnum::fromName('confirmed')) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Blocked'),
                        'code' => '01CSBK',
                        'id' => 'row_blocked',
                        'class' => 'row_blocked',
                        'data-id' => $supplier->id,
                        'data-value' => SupplierStatusEnum::fromName('blocked'),
                        'icon' => 'blocked'
                    ]
                ]
            ]);
        } elseif ($supplier->status === SupplierStatusEnum::fromName('blocked')) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Confirmed'),
                        'code' => '01CSCN',
                        'id' => 'row_confirm',
                        'class' => 'row_confirm',
                        'data-id' => $supplier->id,
                        'data-value' => SupplierStatusEnum::fromName('confirmed'),
                        'icon' => 'confirmed'
                    ]
                ]
            ]);
        }
        $contextMenu->push([
            'label' => __('Statement for ' . $supplier->name_en),
            'code' => '01INLI',
            'id' => 'row_statement',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'statement',
        ]);
        $contextMenu->push([
            'label' => __('Find invoices from ' . $supplier->name_en),
            'code' => '01INLI',
            'id' => 'row_search',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'search',
        ]);

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

}
