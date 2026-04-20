<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Bank;
use App\Support\ShortCuts;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    public function fetchAllRows()
    {
        $rows = Bank::select('id', 'account_holder', 'account_number', 'bank_name', 'branch_name', 'currency', 'iban_code', 'swift_code', 'bank_address', 'sort','company_id');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->account_number,
                //'data-title' => fn($model) => $model->account_number,
                'class' => 'row-item',
                'id' => fn($model) => 'bank-' . strtolower($model->row_no ?? $model->id),
            ])
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $bank = new Bank();
        $shortCuts = ShortCuts::get('banks');

        return view('modules.master.bank.bank-form', [
            'bank' => $bank,
            'shortCuts' => $shortCuts,
        ]);
    }

    public function edit($id)
    {
        $bank = Bank::find($id);
        return view('modules.master.bank.bank-form')->with('bank', $bank);
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'account_holder' => 'required|string|max:255',
            'account_number' => 'required|string|regex:/^[0-9]+$/|max:25',
            'bank' => 'required|string|max:255',
            'branch' => 'required|max:255',
            'iban_code' => 'max:255',
            'swift_code' => 'max:255',
            'currency' => 'required|string|max:10',
            'sort_code' => 'max:2',
            'bank_address' => 'max:255',
        ];

        // Validate request
        $validated = $request->validate($rules);


        // Save customer
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $bank = Bank::findOrFail($request->input('data-id'));
        } else {
            $bank = new Bank();
            $this->setBaseColumns($bank);
        }

        $bank->account_holder = $request->input('account_holder');
        $bank->account_number = $request->input('account_number');
        $bank->bank_name = $request->input('bank');
        $bank->branch_name = $request->input('branch');
        $bank->iban_code = $request->input('iban_code');
        $bank->swift_code = $request->input('swift_code');
        $bank->currency = $request->input('currency');
        $bank->sort = $request->input('sort_code');
        $bank->bank_address = $request->input('bank_address');
        $bank->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Bank created successfully',
            'module_id' => $bank->id,
        ]);
    }

    public function actions($id)
    {
        $bank = Bank::select('id', 'status')->findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $bank->id,
            'type' => 'item',
            'icon' => 'view'
        ], [
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $bank->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $bank->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }

    public function overview($id)
    {
        $bank = Bank::findOrFail($id);
        return view('modules.master.bank.view-overview', compact('bank'));
    }
}
