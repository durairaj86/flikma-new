<?php

namespace App\Http\Controllers\Finance\Asset;

use App\Enums\AssetStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Asset\Asset;
use App\Models\Finance\Asset\AssetCategory;
use App\Models\Finance\Asset\AssetDepreciation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AssetController extends Controller
{
    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = Asset::with(['category:id,name_en,name_ar', 'supplier:id,name_en'])
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($w) use ($s) {
                    $w->where('name_en', 'like', "%{$s}%")
                        ->orWhere('name_ar', 'like', "%{$s}%")
                        ->orWhere('row_no', 'like', "%{$s}%")
                        ->orWhere('invoice_number', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('assets.id');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Asset #' . htmlspecialchars($m->row_no ?? $m->id, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'asset-' . strtolower($m->row_no ?? $m->id),
            ])
            ->addColumn('category_name', fn($m) => $m->category?->name_en ?? '-')
            ->addColumn('supplier_name', fn($m) => $m->supplier?->name_en ?? '-')
            ->editColumn('acquisition_date', fn($m) => $m->acquisition_date)
            ->editColumn('invoice_date', fn($m) => $m->invoice_date ? \Carbon\Carbon::parse($m->invoice_date)->format('d-m-Y') : '-')
            ->addColumn('status_badge', function ($m) {
                $status = (int)$m->status;
                $label = match($status){
                    AssetStatusEnum::CURRENT->value => 'Current',
                    AssetStatusEnum::RUNNING->value => 'Running',
                    AssetStatusEnum::CLOSED->value => 'Closed',
                    default => 'Unknown',
                };
                $cls = match($status){
                    AssetStatusEnum::CURRENT->value => 'secondary',
                    AssetStatusEnum::RUNNING->value => 'info',
                    AssetStatusEnum::CLOSED->value => 'dark',
                    default => 'secondary',
                };
                return '<span class="badge bg-' . $cls . '">' . $label . '</span>';
            })
            ->addColumn('accumulated', fn($m) => number_format($m->depreciations()->sum('amount'), decimals()))
            ->addColumn('book_value', fn($m) => number_format(max(0, $m->cost - $m->depreciations()->sum('amount')), decimals()))
            ->addColumn('actions', function ($m) {
                $id = $m->id;
                return '<div class="btn-group btn-group-sm" role="group">'
                    . '<a href="/finance/asset/' . $id . '/overview" class="btn btn-outline-primary">View</a>'
                    . '</div>';
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }

    public function modal(Request $request)
    {
        $asset = new Asset();
        $categories = AssetCategory::where('is_active', 1)->orderBy('name_en')->get();
        return view('modules.finance.asset.asset-form', compact('asset', 'categories'));
    }

    public function edit(Request $request, $id)
    {
        $asset = Asset::with(['category', 'depreciations'])->findOrFail($id);
        $categories = AssetCategory::where('is_active', 1)->orderBy('name_en')->get();
        return view('modules.finance.asset.asset-form', compact('asset', 'categories'));
    }

    public function store(Request $request)
    {
        $id = $request->input('data-id');
        $data = $request->validate([
            'row_no' => 'nullable|string|max:30',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'acquisition_date' => 'required|date',
            'cost' => 'required',
            'residual_value' => 'nullable',
            'useful_life_months' => 'nullable|integer|min:1|max:600',
            'depreciation_method' => 'nullable|in:straight_line',
            'status' => 'nullable|integer|in:1,2,3',
            'depreciation_start_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data['cost'] = (float)str_replace(',', '', $data['cost']);
        $data['residual_value'] = isset($data['residual_value']) ? (float)str_replace(',', '', $data['residual_value']) : 0.0;

        $asset = Asset::updateOrCreate(['id' => $id], $data);

        return response()->json([
            'status' => 'success',
            'message' => $id ? 'Asset updated successfully' : 'Asset created successfully',
            'id' => $asset->id,
        ]);
    }

    public function actions($id)
    {
        $asset = Asset::select('id', 'row_no', 'status', 'company_id')->findOrFail($id);
        $contextMenu = collect([]);

        // Move to submenu for status changes
        /*$contextMenu->push([
            'label' => __('Move to'),
            'type' => 'submenu',
            'separator' => 'after',
            'icon' => 'move_to',
            'items' => [
                [
                    'label' => __('Current'),
                    'id' => 'row_current',
                    'class' => 'row_current',
                    'data-id' => $asset->id,
                    'type' => 'item',
                    'data-value' => 'current',
                    'icon' => 'pending'
                ],
                [
                    'label' => __('Running'),
                    'id' => 'row_running',
                    'class' => 'row_running',
                    'data-id' => $asset->id,
                    'type' => 'item',
                    'data-value' => 'running',
                    'icon' => 'confirmed'
                ],
                [
                    'label' => __('Closed'),
                    'id' => 'row_closed',
                    'class' => 'row_closed',
                    'data-id' => $asset->id,
                    'type' => 'item',
                    'data-value' => 'closed',
                    'icon' => 'rejected'
                ],
            ]
        ]);*/
        $contextMenu->push([
            'label' => __('Generate Schedule'),
            'id' => 'row_generate_schedule',
            'class' => 'row_generate_schedule',
            'data-id' => $asset->id,
            'type' => 'item',
            'icon' => 'calendar',
            'separator' => 'after',
        ]);
        // Print and View
        $contextMenu->push([
            'label' => __('Print'),
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $asset->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'ASSET.printPreview(' . $asset->id . ')',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $asset->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => 'after',
        ]);

        // Actions submenu
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
                    'data-id' => $asset->id,
                    'type' => 'item',
                    'icon' => 'edit'
                ],
                [
                    'label' => __('Delete'),
                    'id' => 'row_delete',
                    'class' => 'row_delete',
                    'data-id' => $asset->id,
                    'type' => 'item',
                    'icon' => 'delete'
                ],
            ]
        ]);

        return response()->json($contextMenu->values());
    }

    public function updateStatus(Request $request, $id, $status)
    {
        $asset = Asset::findOrFail($id);
        $val = AssetStatusEnum::fromName($status);
        if (!$val) {
            return response()->json(['status' => 'error', 'message' => 'Invalid status'], 422);
        }
        $asset->status = $val;
        $asset->save();
        return response()->json(['status' => 'success', 'message' => 'Status updated']);
    }

    public function overview($id)
    {
        $asset = Asset::with(['category', 'depreciations' => function ($q) {
            $q->orderBy('period_start');
        }])->findOrFail($id);
        return view('modules.finance.asset.view-overview', compact('asset'));
    }

    public function print($id)
    {
        $asset = Asset::with(['category', 'depreciations' => function ($q) {
            $q->orderBy('period_start');
        }])->findOrFail($id);
        return view('modules.finance.asset.asset-print', compact('asset'));
    }

    public function generateSchedule(Request $request, $id)
    {
        $asset = Asset::with('category', 'depreciations')->findOrFail($id);

        $startDate = $asset->depreciation_start_date ? Carbon::parse($asset->depreciation_start_date) : Carbon::parse($asset->acquisition_date)->startOfMonth();

        $base = (float)$asset->cost - (float)$asset->residual_value;
        if ($base <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Invalid base amount for depreciation']);
        }

        $months = $asset->useful_life_months
            ?? $asset->category?->useful_life_months
            ?? null;

        if (!$months && $asset->category?->annual_rate_percent) {
            // Derive months from annual rate (approx): depreciate until residual using monthly rate
            $monthlyRate = ($asset->category->annual_rate_percent / 100) / 12.0;
            if ($monthlyRate <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Invalid depreciation rate']);
            }
            // Fallback to 10 years cap if rate provided without life; still use SL by distributing base over 120 months
            $months = 120;
        }

        if (!$months) {
            return response()->json(['status' => 'error', 'message' => 'Useful life (months) or category rate required']);
        }

        // Wipe future schedule (optional): for simplicity, delete all and regenerate
        $asset->depreciations()->delete();

        $monthly = round($base / $months, 2);
        $accumulated = 0.0;
        $remaining = $base;

        $current = $startDate->copy();
        for ($i = 1; $i <= $months; $i++) {
            $periodStart = $current->copy()->startOfMonth();
            $periodEnd = $current->copy()->endOfMonth();

            $amount = $i < $months ? $monthly : round($remaining, 2); // last month adjust
            $accumulated = round($accumulated + $amount, 2);
            $bookValue = round(max(0, $asset->cost - $accumulated), 2);

            AssetDepreciation::create([
                'asset_id' => $asset->id,
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'amount' => $amount,
                'accumulated' => $accumulated,
                'book_value' => $bookValue,
                'posted' => false,
            ]);

            $remaining = round($remaining - $amount, 2);
            $current->addMonth();
        }

        // Update status if fully depreciated
        $asset->status = $remaining <= 0.0 ? AssetStatusEnum::CLOSED->value : AssetStatusEnum::RUNNING->value;
        $asset->save();

        return response()->json(['status' => 'success', 'message' => 'Depreciation schedule generated']);
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $asset->depreciations()->delete();
        $asset->delete();
        return response()->json(['status' => 'success', 'message' => 'Asset deleted']);
    }
}
