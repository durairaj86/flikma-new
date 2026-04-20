<?php

namespace App\Http\Controllers\Customer;

use App\Enums\CustomerStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yajra\DataTables\Facades\DataTables;
use App\Import\CustomerImport;

class CustomerController extends Controller
{
    /**
     * Import customers from Excel file - Step 1: Upload file and get columns
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

        // Use CustomerImport to get column headers and available fields
        $customerImport = new CustomerImport();
        $headers = $customerImport->getColumnHeaders($path);
        $fields = $customerImport->getAvailableFields();

        return response()->json([
            'columns' => $headers,
            'fields' => $fields,
        ]);
    }

    /**
     * Import customers from Excel file - Step 2: Process the import
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

        // Use CustomerImport to process the import
        $customerImport = new CustomerImport();
        $result = $customerImport->process($path, $mapping);

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
    private static $cache = 'confirmCustomers:';

    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $rows = Customer::select(
            'id',
            'row_no',
            'name_en',
            'name_ar',
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
            'salesperson_id',
            'created_at',
            'company_id'
        )->with('salesperson:id,name')
            ->where('status', CustomerStatusEnum::fromName($request->tab))->orderBy('name_en');

        // Get counts per status in one query
        $statusCounts = Customer::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses (so missing ones appear as 0)
        $allCounts = [];
        foreach (CustomerStatusEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => htmlspecialchars($model->name_en, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'customer-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('business_type', fn($model) => $model->business_type == 'registered' ? ('Registered <br>' . $model->vat_number) : 'Un-registered')
            ->editColumn('created_at', fn($model) => Carbon::parse($model->created_at)->format('d-m-Y'))
            ->rawColumns(['business_type'])
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $customer = new Customer();
        return view('modules.customer.customer-form')->with('customer', $customer);
    }

    public function quickModal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $customer = new Customer();
        return view('modules.customer.quick-customer-form')->with('customer', $customer);
    }

    public function quickStore(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validation rules for quick customer form
        $rules = [
            'quick_customer_name' => 'required|string|max:255',
            'quick_customer_email' => 'required|email|max:255',
            'quick_customer_phone' => 'required|string|max:20',
            'quick_customer_address' => 'nullable|string|max:500',
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create new customer
        $customer = new Customer();
        $customer->unique_row_no = sprintf("%03d", (Customer::max('unique_row_no') ?? 0) + 1);
        $customer->row_no = 'CS' . $customer->unique_row_no;

        // Set base columns
        $this->setBaseColumns($customer);

        // Set customer data from quick form
        $customer->name_en = $request->input('quick_customer_name');
        $customer->name_ar = $request->input('quick_customer_name'); // Using same name for Arabic for quick form
        $customer->email = $request->input('quick_customer_email');
        $customer->phone = $request->input('quick_customer_phone');
        $customer->address1_en = $request->input('quick_customer_address');
        $customer->currency = 'SAR'; // Default currency
        $customer->country = 'SA'; // Default country
        $customer->business_type = 'unregistered'; // Default business type
        $customer->status = 3; // confirmed customer

        $customer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'id' => encodeId($customer->id),
            'name' => $customer->name_en,
            'code' => $customer->row_no,
        ]);
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('modules.customer.customer-form', compact('customer'));
    }

    public function store(Request $request)
    {
        if ($request->input('salesperson_id')) {
            $request->merge(['salesperson_id' => decodeId($request->input('salesperson_id'))]);
        }

        $rules = [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'currency' => 'required|string|max:10',

            // Business type validation (Defaults to nullable)
            'business_type' => 'required|in:registered,unregistered',
            'cr_number' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:50',

            // Credit settings
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|numeric|min:0',

            // Address fields (Defaults to nullable)
            'address1_en' => 'nullable|string|max:255',
            'address1_ar' => 'nullable|string|max:255',
            'address2_en' => 'nullable|string|max:255',
            'address2_ar' => 'nullable|string|max:255',
            'city_en' => 'nullable|string|max:100',
            'city_ar' => 'nullable|string|max:100',
            'building_number' => 'nullable|max:10',
            'plot_no' => 'nullable|max:10',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:25',
            'salesperson_id' => 'nullable',

            // Contact
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:10|max:16|regex:/^\+?[0-9]{10,15}$/',
            'alt_phone' => 'nullable|min:10|max:16',

            // Logistics
            'preferred_shipping' => 'nullable|string|max:50',
            'preferred_carrier' => 'nullable|string|max:100',
            'default_port' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:34',
            'payment_terms' => 'nullable|string|max:500',
        ];

        if ($request->business_type === 'registered') {
            // Business Registration mandatory fields
            $rules['cr_number'] = 'required|string|max:50';
            $rules['vat_number'] = 'required|string|max:50';

            // Address mandatory fields when business_type is 'registered'
            $rules['address1_en'] = 'required|string|max:255';
            $rules['city_en'] = 'required|string|max:100';
            $rules['building_number'] = 'required|max:10';
            $rules['plot_no'] = 'required|max:10';
            $rules['postal_code'] = 'required|string|max:20';
        }

        // Validate request
        $validated = $request->validate($rules);

        if (isset($request['data-id']) and filled($request['data-id'])) {
            $customer = Customer::findOrFail($request->input('data-id'));
        } else {
            $customer = new Customer();
            $customer->unique_row_no = sprintf("%03d", (Customer::max('unique_row_no') ?? 0) + 1);
            $customer->row_no = 'CS' . $customer->unique_row_no;

            $this->setBaseColumns($customer);
        }

        $customer->name_en = $validated['name_en'];
        $customer->name_ar = $validated['name_ar'];
        $customer->currency = $validated['currency'];
        $customer->business_type = $validated['business_type'];
        $customer->cr_number = $validated['cr_number'] ?? null;
        $customer->vat_number = $validated['vat_number'] ?? null;
        $customer->credit_limit = $validated['credit_limit'];
        $customer->credit_days = $validated['credit_days'];
        $customer->address1_en = $validated['address1_en'];
        $customer->address1_ar = $validated['address1_ar'];
        $customer->city_en = $validated['city_en'];
        $customer->city_ar = $validated['city_ar'];
        $customer->building_number = $validated['building_number'];
        $customer->plot_no = $validated['plot_no'];
        $customer->postal_code = $validated['postal_code'];
        $customer->country = $validated['country'];
        $customer->email = $validated['email'];
        $customer->phone = $validated['phone'];
        $customer->alt_phone = $validated['alt_phone'];
        $customer->default_port = $validated['default_port'];
        $customer->preferred_shipping = $validated['preferred_shipping'];
        $customer->payment_terms = $validated['payment_terms'];
        $customer->salesperson_id = $validated['salesperson_id'];
        $customer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'customer_id' => $customer->id,
        ]);
    }

    public function updateStatus($id, $status)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = $status; // e.g., confirmed = 2
        $customer->save();

        Cache::forget(self::$cache . cacheName());
        return response()->json([
            'status' => 'success',
            'message' => 'Customer status updated successfully!',
            'data' => [
                'id' => $customer->id,
                'status' => $customer->status, // numeric (0,1,2..)
                'label' => \App\Enums\CustomerStatusEnum::from($customer->status)->label(), // "Confirmed"
                'color' => \App\Enums\CustomerStatusEnum::from($customer->status)->color(),
            ],
        ]);
    }

    public function actions($id)
    {
        $customer = Customer::select('id', 'row_no', 'name_en', 'name_ar', 'status')->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        if ($customer->status === CustomerStatusEnum::fromName('pending')) {
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
                        'data-id' => $customer->id,
                        'data-value' => CustomerStatusEnum::fromName('confirmed'),
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Rejected'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $customer->id,
                        'data-value' => CustomerStatusEnum::fromName('rejected'),
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($customer->status === CustomerStatusEnum::fromName('confirmed')) {
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
                        'data-id' => $customer->id,
                        'data-value' => CustomerStatusEnum::fromName('blocked'),
                        'icon' => 'blocked'
                    ]
                ]
            ]);
        } elseif ($customer->status === CustomerStatusEnum::fromName('blocked')) {
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
                        'data-id' => $customer->id,
                        'data-value' => CustomerStatusEnum::fromName('confirmed'),
                        'icon' => 'confirmed'
                    ]
                ]
            ]);
        } elseif ($customer->status === CustomerStatusEnum::fromName('rejected')) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Pending'),
                        'code' => '01CSCN',
                        'id' => 'row_pending',
                        'class' => 'row_pending',
                        'data-id' => $customer->id,
                        'data-value' => CustomerStatusEnum::fromName('pending'),
                        'icon' => 'pending'
                    ]
                ]
            ]);
        }

        if ($customer->status !== CustomerStatusEnum::fromName('rejected')) {
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
                'code' => '01CSDL',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'delete'
            ];
        }
        $contextMenu->push([
            'label' => __('Send Email'),
            'code' => '01CSEM',
            'id' => 'row_email',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'email',
        ]);
        if ($customer->status === CustomerStatusEnum::fromName('confirmed') || $customer->status === CustomerStatusEnum::fromName('blocked')) {
            $contextMenu->push([
                'label' => __('Statement for ' . Str::limit($customer->name_en, 15, '...')),
                'code' => '01INLI',
                'id' => 'row_statement',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'statement',
                'separator' => 'before',
            ]);
            $contextMenu->push([
                'label' => __('Find invoices from ' . Str::limit($customer->name_en, 15, '...')),
                'code' => '01INLI',
                'id' => 'row_search',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'search',
            ]);
        }

        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('Actions'),
            'type' => 'submenu',
            'icon' => 'action',
            'items' => array_merge([$edit], [$delete])
        ]);

        // Direct menu items
        /*$contextMenu->push([
            'label' => __('Create Job'),
            'code' => '01CSJB',
            'id' => 'row_job',
            'class' => 'row_job',
            'data-id' => $customer->id,
            'type' => 'item'
        ]);

        $contextMenu->push([
            'label' => __('Create Quotation'),
            'code' => '01CSQT',
            'id' => 'row_quotation',
            'class' => 'row_quotation',
            'data-id' => $customer->id,
            'type' => 'item'
        ]);*/

        // Grouped "Actions" submenu
        /*$contextMenu->push([
            'label' => __('Actions'),
            'type' => 'submenu',
            'items' => [
                [
                    'label' => __('View'),
                    'code' => '01CSVW',
                    'id' => 'row_view',
                    'class' => 'row_view',
                    'data-id' => $customer->id,
                ],
                [
                    'label' => __('Edit'),
                    'code' => '01CSED',
                    'id' => 'row_edit',
                    'class' => 'row_edit',
                    'data-id' => $customer->id,
                ],
                [
                    'label' => __('Delete'),
                    'code' => '01CSDL',
                    'id' => 'row_delete',
                    'class' => 'row_delete',
                    'data-id' => $customer->id,
                ]
            ]
        ]);*/

        return response()->json($contextMenu->values());
    }

    public function overview($id)
    {
        $customer = Customer::findOrFail($id);
        return view('modules.customer.view-overview', compact('customer'));
    }

    /*public function invoices($id) {
        $invoices = Invoice::where('customer_id', $id)->paginate(5);
        return view('customers.ajax.invoices', compact('invoices'));
    }

    public function transactions($id) {
        $transactions = Transaction::where('customer_id', $id)->paginate(5);
        return view('customers.ajax.transactions', compact('transactions'));
    }*/

}
