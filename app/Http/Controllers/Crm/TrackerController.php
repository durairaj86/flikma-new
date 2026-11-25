<?php

namespace App\Http\Controllers\Crm;

use App\Exports\ReportTableExport;
use App\Http\Controllers\Controller;
use App\Models\Crm\CrmAction;
use App\Models\Master\Salesperson\Salesperson;
use App\Services\Crm\CrmSettings;
use App\Services\Crm\CustomerTracker;
use App\Services\Crm\SalesScope;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

/** Customer Tracker: the sales workbook's CRM sheet, one line per customer / prospect with its automatic columns. */
class TrackerController extends Controller
{
    public function index()
    {
        $own = SalesScope::ownSalespersonIds();
        $salespersons = Salesperson::orderBy('name')->when($own, fn($q) => $q->whereIn('id', $own))->get(['id', 'name']);

        return view('modules.crm.tracker', compact('salespersons', 'own'));
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $rows = $this->filtered($request);
        $counts = [
            'lines' => $rows->count(),
            'stale' => $rows->where('stale', true)->count(),
            'overdue' => $rows->where('next_overdue', true)->count(),
        ];

        return DataTables::collection($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-type' => fn($r) => $r['type'],
                'data-id' => fn($r) => $r['id'],
                'class' => 'row-item',
            ])
            ->addColumn('status_label', fn($r) => __($r['status']))
            ->addColumn('kind', fn($r) => $r['type'] === 'customer' ? __('Customer') : __('Prospect'))
            ->addColumn('last_style', fn($r) => CrmAction::badgeStyle($r['last_category']))
            ->editColumn('last_at', fn($r) => $r['last_at'] ? date('d-m-Y', strtotime($r['last_at'])) : null)
            ->editColumn('next_at', fn($r) => $r['next_at'] ? date('d-m-Y', strtotime($r['next_at'])) : null)
            ->addColumn('selling_fmt', fn($r) => $r['selling'] !== null ? amountFormat($r['selling']) : null)
            ->addColumn('margin_fmt', fn($r) => $r['margin'] !== null ? amountFormat($r['margin']) : null)
            ->with(['counts' => $counts, 'stale_days' => CrmSettings::get('stale_days')])
            ->toJson();
    }

    public function export(Request $request)
    {
        $rows = $this->filtered($request);
        $cols = [__('Customer / Company'), __('Type'), __('Rank'), __('Suggested Rank'), __('Status'), __('Contact Person'), __('Contact No'), __('Email'),
            __('Location'), __('Sector / Route'), __('Service Type'), __('Approx Volume'), __('Incoterm'), __('Handled By'), __('Approx Cost'), __('Approx Selling'),
            __('Margin'), __('Margin %'), __('Touches'), __('RFQs'), __('Jobs'), __('Score'), __('Last Entry Date'), __('Last Entry'), __('Days Since'),
            __('Next Planned Date'), __('Next Planned')];
        $data = $rows->map(fn($r) => [
            $r['name'], $r['type'] === 'customer' ? __('Customer') : __('Prospect'), $r['rank'], $r['suggested_rank'], __($r['status']), $r['contact'], $r['phone'],
            $r['email'], $r['location'], $r['route'], $r['service'], $r['volume'], $r['incoterm'], $r['handled_by'], $r['cost'], $r['selling'], $r['margin'],
            $r['margin_pct'], $r['touches'], $r['rfqs'], $r['jobs'], $r['score'], $r['last_at'] ? date('d-m-Y', strtotime($r['last_at'])) : null,
            $r['last_action'], $r['days_since'], $r['next_at'] ? date('d-m-Y', strtotime($r['next_at'])) : null, $r['next_action'],
        ])->all();
        $totals = array_fill(0, count($cols), null);
        $totals[0] = __('Total') . ' (' . count($data) . ')';
        $totals[14] = $rows->sum('cost');
        $totals[15] = $rows->sum('selling');
        $totals[16] = $rows->sum('margin');

        return Excel::download(new ReportTableExport($data, $totals, $cols, [
            'title' => __('Customer Tracker'),
            'lines' => [now()->format('d-m-Y')],
            // phone as typed; cost / selling / margin / margin % as money; touches, RFQs, jobs, score, days as counts
            'text_columns' => [7],
            'money_columns' => [15, 16, 17, 18],
            'integer_columns' => [19, 20, 21, 22, 25],
        ]), 'customer_tracker.xlsx');
    }

    /** Lines the user may see, narrowed by the filters (salesperson, rank, status, "needs attention", search). */
    private function filtered(Request $request): Collection
    {
        $own = SalesScope::ownSalespersonIds();
        $picked = $request->filled('salesperson_id') ? (int) $request->input('salesperson_id') : null;
        // a salesman can narrow to one of his own records, never widen past them
        $scope = $own ? ($picked && in_array($picked, $own, true) ? [$picked] : $own) : ($picked ? [$picked] : null);

        $rows = (new CustomerTracker())->rows($scope);
        $show = $request->input('show', 'open');
        $term = mb_strtolower(trim((string) ($request->input('q') ?? $request->input('search.value'))));

        return $rows
            ->when($show === 'open', fn($c) => $c->reject(fn($r) => in_array($r['status'], ['Lost', 'Inactive'], true)))
            ->when($show === 'attention', fn($c) => $c->filter(fn($r) => $r['stale'] || $r['next_overdue']))
            ->when($request->filled('rank'), fn($c) => $c->where('rank', $request->input('rank')))
            ->when($request->filled('status'), fn($c) => $c->where('status', $request->input('status')))
            ->when($term !== '', fn($c) => $c->filter(fn($r) => str_contains(mb_strtolower(implode(' ', [$r['name'], $r['no'], $r['contact'], $r['phone'], $r['email'], $r['route'], $r['location']])), $term)))
            ->sortBy([['stale', 'desc'], ['next_overdue', 'desc'], ['score', 'desc']])
            ->values();
    }
}
