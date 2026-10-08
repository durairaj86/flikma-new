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
                        'message' => __('You are not allowed to edit this account'),
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
                'message' => __('Account added successfully'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Validation failed.'),
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
            })
            ->when(($request->filterData['status'] ?? 'all') === 'active', fn($q) => $q->where('is_active', 1))
            ->when(($request->filterData['status'] ?? 'all') === 'inactive', fn($q) => $q->where('is_active', 0))
            ->when(!empty($request->filterData['parent']), fn($q) => $q->where('parent_id', (int) $request->filterData['parent']));

        // Counts per account type in one query
        $allCounts = Account::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Chart of accounts as a tree: children follow their parent (depth-first), so the list can be indented / collapsed.
        // An account whose parent isn't in the filtered set (other type, filtered out) is shown as a root.
        $list = $rows->orderBy('code')->get();
        $present = $list->pluck('id')->flip();
        $byParent = $list->groupBy(fn($a) => $a->parent_id && isset($present[$a->parent_id]) ? $a->parent_id : 0);
        $flat = collect();
        $walk = function ($parent, $depth, $ancestors) use (&$walk, $byParent, &$flat) {
            foreach ($byParent[$parent] ?? [] as $acc) {
                $acc->depth = $depth;
                $acc->ancestors = implode(',', $ancestors);
                $acc->has_children = isset($byParent[$acc->id]);
                $flat->push($acc);
                $walk($acc->id, $depth + 1, array_merge($ancestors, [$acc->id]));
            }
        };
        $walk(0, 0, []);

        return DataTables::collection($flat)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-ancestors' => fn($model) => $model->ancestors,
                'data-depth' => fn($model) => $model->depth,
                'class' => 'row-item',
            ])
            ->editColumn('is_active', fn($row) => (bool)$row->is_active)
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

    /**
     * Delete an account — refused while anything still uses it (sub accounts, ledger entries,
     * invoice / expense / payment lines, items, journal lines) or when it is a default / system account.
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $account = Account::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('account', (int) $id);
        if ($account->company_id != companyId()) {
            $why[] = __('it is a default account of the chart of accounts');
        }
        if ($why) {
            return $guard->refusal(__('account'), array_values(array_unique($why)));
        }

        $account->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Account deleted successfully'),
        ]);
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $account = Account::findOrFail($id);
        $account->is_active = $status == 'true' ? 1 : 0;
        $account->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Account status updated successfully!'),
            'data' => [
                'id' => $account->id,
                'status' => $account->status,
                'label' => $account->status == 1 ? 'Active' : 'In-active',
            ],
        ]);
    }

}
