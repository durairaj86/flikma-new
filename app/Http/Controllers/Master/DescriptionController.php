<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Master\Description;
use App\Support\CompanyDefaults;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DescriptionController extends Controller
{
    public function fetchAllRows()
    {
        $accounts = Account::all()->pluck('name', 'id')->toArray();
        $rows = Description::select('id', 'description', 'description_local', 'sale_account_id', 'purchase_account_id', 'is_active', 'created_at', 'company_id');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->account_number,
                //'data-title' => fn($model) => $model->account_number,
                'class' => 'row-item',
                'id' => fn($model) => 'description-' . strtolower($model->row_no ?? $model->id),
            ])
            // on / off as this company sees it: a shared row it has switched off for itself counts as off
            ->editColumn('is_active', fn ($row) => $this->isOn($row) ? 1 : 0)
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
            'message' => __('Description created successfully'),
            'module_id' => $description->id,
        ]);
    }

    /** Is this description on for the current company? Off when the row is off, or it is a shared row this company switched off. */
    private function isOn(Description $row): bool
    {
        if (!$row->is_active) {
            return false;
        }

        return !($row->company_id === null && in_array((int) $row->id, CompanyDefaults::switchedOff('descriptions', (int) companyId()), true));
    }

    /**
     * Edit, and Active / In-Active. There is no Delete: a description may be on invoices, quotations and credit notes already, so it is
     * switched off (no longer offered in new dropdowns) instead. A shared description (company_id empty) belongs to every company, so
     * switching it off only records that for this company; the row itself, and every other company, are not touched.
     */
    public function actions($id)
    {
        $description = Description::select('id', 'is_active', 'company_id')->findOrFail($id);

        $contextMenu = collect([]);

        $contextMenu->push($this->isOn($description) ? [
            'label' => __('In-Active'),
            'code' => '01CSED',
            'id' => 'row_inactive',
            'class' => 'row_inactive',
            'data-id' => $description->id,
            'data-value' => '0',
            'type' => 'item',
            'icon' => 'blocked'
        ] : [
            'label' => __('Active'),
            'code' => '01CSED',
            'id' => 'row_active',
            'class' => 'row_active',
            'data-id' => $description->id,
            'data-value' => '1',
            'type' => 'item',
            'icon' => 'confirmed'
        ]);

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $description->id,
            'type' => 'item',
            'icon' => 'edit'
        ]);

        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        abort_unless(in_array((string) $status, ['0', '1'], true), 404);
        $description = Description::findOrFail($id);
        $on = (string) $status === '1';

        if ($description->company_id === null) {
            // shared: only this company's own switch changes
            CompanyDefaults::setSharedActive('descriptions', (int) companyId(), (int) $description->id, $on);
        } else {
            $description->is_active = $on ? 1 : 0;
            $description->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => $on ? __('Description activated.') : __('Description made inactive. It is no longer offered in dropdowns; documents that already use it keep showing it.'),
            'data' => ['id' => $description->id, 'status' => $on ? 1 : 0],
        ]);
    }
}
