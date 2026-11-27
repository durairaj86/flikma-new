<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Master\Bank;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () use ($bank) {
            $isNew = !$bank->exists;
            $bank->save();
            $isNew ? $this->createAccount($bank) : $this->syncAccount($bank);
        });

        return response()->json([
            'status' => 'success',
            'message' => __('Bank created successfully'),
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

    /** The chart-of-accounts side of a bank: its own posting account under 1120 Bank Accounts, so Paid From / Received In can use it. */
    private function createAccount(Bank $bank): void
    {
        $parent = Account::query()->where('code', '1120')->first();
        if (!$parent) {
            return;
        }
        $taken = Account::withoutGlobalScopes()->whereNotNull('code')->pluck('code')->map(fn($c) => (string) $c)->flip();
        $code = null;
        // 1122 - 1129 first (1130 is Accounts Receivable), then 11201, 11202 ...
        foreach (array_merge(range(1122, 1129), range(11201, 11299)) as $try) {
            if (!isset($taken[(string) $try])) {
                $code = (string) $try;
                break;
            }
        }
        if (!$code) {
            return;
        }
        $account = new Account();
        $this->setBaseColumns($account);
        $account->name = $this->accountName($bank);
        $account->code = $code;
        $account->type = $parent->type;
        $account->parent_id = $parent->id;
        $account->is_grouped = 0;
        $account->is_last = 1;
        $account->is_level = $parent->is_level + 1;
        $account->is_active = 1;
        $account->account_number = $bank->account_number;
        $account->currency = $bank->currency;
        $account->save();
        $bank->account_id = $account->id;
        $bank->save();
    }

    /** Renaming the bank renames its account (the code and the entries stay). */
    private function syncAccount(Bank $bank): void
    {
        $account = $bank->account_id ? Account::query()->where('company_id', companyId())->find($bank->account_id) : null;
        if ($account) {
            $account->name = $this->accountName($bank);
            $account->account_number = $bank->account_number;
            $account->save();
        }
    }

    private function accountName(Bank $bank): string
    {
        return trim($bank->bank_name . ' - ' . substr((string) $bank->account_number, -4));
    }

    /** Delete — refused (HTTP 422 + the reasons) while anything still uses this record. */
    public function delete($id)
    {
        $model = Bank::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('bank', (int) $id);
        if ($why) {
            return $guard->refusal(__('bank'), $why);
        }

        // its account holds entries (or is used on a document): the bank stays
        $account = $model->account_id ? Account::query()->where('company_id', companyId())->find($model->account_id) : null;
        DB::beginTransaction();
        {
            if ($account) {
                // the bank's own link to the account does not count as a use of it
                DB::table('banks')->where('id', $model->id)->update(['account_id' => null]);
                $used = $guard->blockers('account', (int) $account->id);
                if ($used) {
                    DB::rollBack();

                    return response()->json([
                        'status' => 'error',
                        'message' => __('Transactions already exist under this bank, so it cannot be deleted.'),
                        'reasons' => $used,
                        'hint' => __('Transactions already exist under this bank, so it cannot be deleted.'),   // shown under the list in the alert
                    ], 422);
                }
            }
            $model->delete();
            $account?->delete();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => __('Bank deleted successfully')]);
        }
    }
}
