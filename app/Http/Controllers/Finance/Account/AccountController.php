<?php

namespace App\Http\Controllers\Finance\Account;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('parent')->get();
        return view('modules.finance.accounts.index', compact('accounts'));
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $account = new Account();
        $allAccounts = Account::where('is_active', 1)
            ->where('is_level', '>', 0)
            ->orderBy('type')
            ->orderBy('is_level') // ensures parent-level first
            ->get();

        $accountTypes = $allAccounts->groupBy('type');

// Helper to build full path like "Parent -> Child -> Subchild"
        $buildPath = function ($acc) use ($allAccounts) {
            $names = [];
            $current = $acc;

            while ($current) {
                if (is_array($current)) {
                    $names[] = $current['name'] ?? '';
                    $parentId = $current['parent_id'] ?? null;
                    $current = $parentId ? $allAccounts->firstWhere('id', $parentId) : null;
                } else {
                    $names[] = $current->name ?? '';
                    $parentId = $current->parent_id ?? null;
                    $current = $parentId ? $allAccounts->firstWhere('id', $parentId) : null;
                }
            }

            $names = array_reverse(array_filter($names, fn($n) => $n !== ''));
            return implode(' -> ', $names);
        };
        return view('modules.finance.accounts.account-form', compact('account', 'accountTypes', 'buildPath'));
    }

    public function edit($id): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $account = Account::findOrFail($id);
        $allAccounts = Account::where('is_level', '>', 0)
            ->orderBy('type')
            ->orderBy('is_level') // ensures parent-level first
            ->get();

        $accountTypes = $allAccounts->groupBy('type');

// Helper to build full path like "Parent -> Child -> Subchild"
        $buildPath = function ($acc) use ($allAccounts) {
            $names = [];
            $current = $acc;

            while ($current) {
                if (is_array($current)) {
                    $names[] = $current['name'] ?? '';
                    $parentId = $current['parent_id'] ?? null;
                    $current = $parentId ? $allAccounts->firstWhere('id', $parentId) : null;
                } else {
                    $names[] = $current->name ?? '';
                    $parentId = $current->parent_id ?? null;
                    $current = $parentId ? $allAccounts->firstWhere('id', $parentId) : null;
                }
            }

            $names = array_reverse(array_filter($names, fn($n) => $n !== ''));
            return implode(' -> ', $names);
        };
        return view('modules.finance.accounts.account-form', compact('account', 'accountTypes', 'buildPath'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'account_name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:accounts,id',
                'account_code' => [
                    'nullable',
                    'regex:/^\d{4,6}$/', // 4–6 digits only
                    Rule::unique('accounts', 'code')->where(function ($q) use ($request) {
                        return $q->where(function ($q) use ($request) {
                            $q->where('company_id', companyId())->orWhereNull('company_id');
                        });
                    })->ignore($request['data-id']),
                ],
                'description' => 'nullable|string|max:255',
                'currency' => 'nullable|string|max:10',
                'account_number' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            // Default values
            $validated['is_grouped'] = 0;
            $validated['is_last'] = 1;
            $validated['is_level'] = 0;

            DB::beginTransaction();

            if (isset($request['data-id']) and filled($request['data-id'])) {
                $account = Account::findOrFail($request->input('data-id'));
                if ($account->company_id !== companyId()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You are not allowed to edit this account',
                    ]);
                }
            } else {
                $account = new Account();
                $this->setBaseColumns($account);
            }

            // If has parent
            if (!empty($request->parent_id)) {
                $parent = Account::find($request->parent_id);

                if ($parent) {
                    // Mark parent as grouped
                    $parent->update([
                        'is_grouped' => 1,
                        'is_last' => 0,
                    ]);

                    // Inherit properties
                    $validated['is_level'] = $parent->is_level + 1;
                    $validated['type'] = $parent->type;
                }
            }

            // Create new account
            $account->name = $validated['account_name'];
            $account->code = $validated['account_code'];
            $account->parent_id = $validated['parent_id'];
            $account->is_active = $validated['is_active'] ?? 0;
            $account->description = $validated['description'];
            $account->account_number = $validated['account_number'];
            $account->is_grouped = $validated['is_grouped'];
            $account->is_last = $validated['is_last'];
            $account->is_level = $validated['is_level'];
            $account->currency = $validated['currency'];
            $account->type = $validated['type'];
            $account->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Account added successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $rows = Account::select('id', 'code', 'name', 'parent_id', 'is_level', 'is_active', 'is_grouped', 'account_number', 'is_last', 'type', 'company_id')
            ->when($request->type, function ($q) use ($request) {
                $q->where('type', ucfirst($request->type));
            });

        $allAccounts = $rows->get()->pluck('name', 'id')->toArray();
        // Get counts per status in one query
        $allCounts = Account::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();


        return DataTables::eloquent($rows)
            ->addIndexColumn() // gives DT_RowIndex
            ->setRowAttr([
                'data-id' => function ($model) {
                    return $model->id;
                },
                'class' => 'row-item',
            ])
            ->addColumn('parent_name', function ($row) {
                return optional($row->parent)->name ?? '-';
            })
            ->editColumn('is_active', function ($row) {
                return (bool)$row->is_active;
            })
            ->addColumn('row_no', function ($row) {
                // optional: alias DT_RowIndex to row_no if your JS expects row_no
                return $row->DT_RowIndex ?? '';
            })
            ->editColumn('parent_id', function ($row) use ($allAccounts) {
                return $allAccounts[$row->parent_id] ?? '-';
            })
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();
    }

    public function actions($id)
    {
        $account = Account::select('id', 'is_active', 'company_id')->findOrFail($id);

        $contextMenu = collect([]);
        if ($account->company_id == companyId()) {

            // Direct menu items
            $contextMenu->push([
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $account->id,
                'type' => 'item',
                'icon' => 'edit'
            ], [
                'label' => __('Delete'),
                'code' => '01CSDL',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $account->id,
                'type' => 'item',
                'icon' => 'delete'
            ]);
        }

        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $account = Account::findOrFail($id);
        $account->is_active = $status == 'true' ? 1 : 0;
        $account->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Account status updated successfully!',
            'data' => [
                'id' => $account->id,
                'status' => $account->status,
                'label' => $account->status == 1 ? 'Active' : 'In-active',
            ],
        ]);
    }

}
