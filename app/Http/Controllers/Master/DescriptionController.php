<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Master\Description;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DescriptionController extends Controller
{
    public function fetchAllRows()
    {
        $accounts = Account::all()->pluck('name', 'id')->toArray();
        $rows = Description::select('id', 'description', 'description_local', 'sale_account_id', 'purchase_account_id', 'created_at', 'company_id');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->account_number,
                //'data-title' => fn($model) => $model->account_number,
                'class' => 'row-item',
                'id' => fn($model) => 'description-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('created_at', function ($row) {
                return showDate($row->created_at);
            })
            ->editColumn('sale_account_id', function ($row) use ($accounts) {
                return $accounts[$row->sale_account_id] ?? null;
            })
            ->editColumn('purchase_account_id', function ($row) use ($accounts) {
                return $accounts[$row->purchase_account_id] ?? null;
            })
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $model = new Description();

        $salesParents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts
        $salesSubAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();

        $purchaseParents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts
        $purchaseSubAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();

        return view('modules.master.description.description-form', [
            'description' => $model,
            'salesParents' => $salesParents,
            'salesSubAccounts' => $salesSubAccounts,
            'purchaseParents' => $purchaseParents,
            'purchaseSubAccounts' => $purchaseSubAccounts,
        ]);
    }

    public function edit($id)
    {
        $salesParents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts
        $salesSubAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();

        $purchaseParents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts
        $purchaseSubAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();

        $model = Description::find($id);
        return view('modules.master.description.description-form', [
            'description' => $model,
            'salesParents' => $salesParents,
            'salesSubAccounts' => $salesSubAccounts,
            'purchaseParents' => $purchaseParents,
            'purchaseSubAccounts' => $purchaseSubAccounts,
        ]);
    }

    public function store(Request $request)
    {
        $id = null;
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $id = $request['data-id'];
        }
        // Common validation rules
        $companyId = companyId();
        $rules = [
            'description' => [
                'required',
                'string',
                'max:128',
                Rule::unique('descriptions', 'description')
                    ->where('company_id', $companyId)
                    ->ignore($id) // only if updating
            ],
            'description_local' => [
                'required',
                'string',
                'max:128',
                Rule::unique('descriptions', 'description_local')
                    ->where('company_id', $companyId)
                    ->ignore($id) // only if updating
            ],
        ];

        // Validate request
        $validated = $request->validate($rules);


        // Save customer
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $description = Description::findOrFail($request->input('data-id'));
        } else {
            $description = new Description();
            $this->setBaseColumns($description);
        }

        $description->description = $request->input('description');
        $description->description_local = $request->input('description_local');
        $description->sale_account_id = $request->input('sale_account');
        $description->purchase_account_id = $request->input('purchase_account');
        $description->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Description created successfully',
            'module_id' => $description->id,
        ]);
    }

    public function actions($id)
    {
        $description = Description::select('id')->findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $description->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $description->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }
}
