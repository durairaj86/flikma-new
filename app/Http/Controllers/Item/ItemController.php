<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Models\Item\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    private static $cache = 'items:';

    /**
     * Display the items listing page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('modules.inventory.item.list');
    }

    /**
     * Fetch all items for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllRows(Request $request)
    {
        $rows = Item::select(
            'id',
            'unique_number',
            'sku_code',
            'name_en',
            'name_ar',
            'account_type',
            'cost_price',
            'selling_price',
            'created_at',
            'company_id'
        )->where('is_active', 1)
         ->orderBy('name_en');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => htmlspecialchars($model->name_en, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'item-' . strtolower($model->unique_number ?? $model->id),
            ])
            ->editColumn('created_at', fn($model) => \Carbon\Carbon::parse($model->created_at)->format('d-m-Y'))
            ->toJson();
    }

    /**
     * Display the item creation modal.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function modal()
    {
        $item = new Item();

        // Get expense and income accounts
        $costAccounts = \App\Models\Finance\Account\Account::where('is_active', 1)
            ->where('type', 'Expense')
            ->orderBy('name')
            ->get();

        $salesAccounts = \App\Models\Finance\Account\Account::where('is_active', 1)
            ->where('type', 'Income')
            ->orderBy('name')
            ->get();

        return view('modules.inventory.item.item-form', compact('item', 'costAccounts', 'salesAccounts'));
    }

    /**
     * Display the item edit modal.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $item = Item::findOrFail($id);

        // Get expense and income accounts
        $costAccounts = \App\Models\Finance\Account\Account::where('is_active', 1)
            ->where('type', 'Expense')
            ->orderBy('name')
            ->get();

        $salesAccounts = \App\Models\Finance\Account\Account::where('is_active', 1)
            ->where('type', 'Income')
            ->orderBy('name')
            ->get();

        return view('modules.inventory.item.item-form', compact('item', 'costAccounts', 'salesAccounts'));
    }

    /**
     * Store a new item or update an existing one.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'account_type' => 'required|string|in:expense,income',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'cost_account_id' => 'nullable|exists:accounts,id',
            'sales_account_id' => 'nullable|exists:accounts,id',
        ];

        // Add custom validation for selling price > cost price when cost price exists
        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $costPrice = $request->input('cost_price');
            $sellingPrice = $request->input('selling_price');

            if (!empty($costPrice) && !empty($sellingPrice) && $costPrice !=0 && $sellingPrice <= $costPrice) {
                $validator->errors()->add('selling_price', 'Selling price must be greater than cost price');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate request
        $validated = $validator->validated();

        if (isset($request['data-id']) && filled($request['data-id'])) {
            $item = Item::findOrFail($request->input('data-id'));
        } else {
            $item = new Item();
            $lastNumber = Item::where('company_id', companyId())->max('unique_number');
            $skuCode = ($lastNumber ?? 0) + 1;
            $item->unique_number = $skuCode;
            $item->sku_code = 'SKU-' . companyId() . '-' . sprintf('%03d', $skuCode);
            $item->company_id = companyId();
            $item->branch_id = 1;

            $this->setBaseColumns($item);
        }

        $item->name_en = $validated['name_en'];
        $item->name_ar = $validated['name_ar'];
        $item->account_type = $validated['account_type'];
        $item->cost_price = $validated['cost_price'];
        $item->selling_price = $validated['selling_price'];
        $item->save();

        Cache::forget(self::$cache . cacheName());

        return response()->json([
            'status' => 'success',
            'message' => 'Item ' . (isset($request['data-id']) ? 'updated' : 'created') . ' successfully',
            'id' => $item->id,
        ]);
    }

    /**
     * Create a new item (moved from CommonController).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createItem(Request $request)
    {
        $companyId = companyId();
        $branchId = 1;
        $name = trim($request->input('name'));

        // 1. Check if item already exists
        $existingItem = Item::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('name_en', $name)
            ->first();

        if ($existingItem) {
            return response()->json([
                'id' => $existingItem->id,
                'name' => $existingItem->name_en
            ]);
        }

        // 2. Create new item
        $lastNumber = Item::where('company_id', $companyId)->max('unique_number');
        $skuCode = ($lastNumber ?? 0) + 1;

        $item = new Item();
        $item->company_id = $companyId;
        $item->branch_id = $branchId;
        $item->unique_number = $skuCode;
        $item->sku_code = 'SKU-' . $companyId . '-' . sprintf('%03d', $skuCode);
        $item->name_en = $name;
        $item->name_ar = $name;
        $item->account_type = 'expense';

        $this->setBaseColumns($item);
        $item->save();

        Cache::forget(self::$cache . cacheName());

        return response()->json([
            'id' => $item->id,
            'name' => $item->name_en,
            'subtext' => $item->sku_code,
        ]);
    }

    /**
     * Delete an item.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $item = Item::findOrFail($id);
        $item->is_active = 0;
        $item->deleted_at = now();
        $item->save();

        Cache::forget(self::$cache . cacheName());

        return response()->json([
            'status' => 'success',
            'message' => 'Item deleted successfully',
        ]);
    }

    /**
     * Display the item details view.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function view($id)
    {
        $item = Item::findOrFail($id);
        return view('modules.inventory.item.view-overview', compact('item'));
    }

    /**
     * Get item actions for context menu.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function actions($id)
    {
        $item = Item::select('id', 'name_en', 'name_ar', 'is_active')->findOrFail($id);
        $contextMenu = collect([]);

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01ITED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $item->id,
            'type' => 'item',
            'icon' => 'edit'
        ]);

        $contextMenu->push([
            'label' => __('Delete'),
            'code' => '01ITDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $item->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);

        $contextMenu->push([
            'label' => __('View'),
            'code' => '01ITVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $item->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => 'before',
        ]);

        return response()->json($contextMenu->values());
    }
}
