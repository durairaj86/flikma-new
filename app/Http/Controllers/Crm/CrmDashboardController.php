<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\CrmActivity;
use App\Models\Master\Salesperson\Salesperson;
use App\Services\Crm\CrmDashboard;
use App\Services\Crm\SalesScope;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** CRM Dashboard: the sales workbook's Dashboard sheet for a viewing date (today unless another date is chosen). */
class CrmDashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('modules.crm.dashboard', $this->data($request));
    }

    /** The dashboard as an Excel sheet in the sales workbook's Dashboard layout. */
    public function export(Request $request)
    {
        $data = $this->data($request);
        $company = \Illuminate\Support\Facades\DB::table('companies')->where('id', companyId())->first(['name', 'base_currency']);
        $who = $data['picked'] ? ($data['salespersons']->firstWhere('id', $data['picked'])->name ?? '') : ($data['own'] ? __('All mine') : __('All Salesmen'));

        // the TRENDS charts: the last 26 weeks and 12 months up to the viewing date
        $data['weekly'] = $data['dash']->trend('week', 26);
        $data['monthly'] = $data['dash']->trend('month', 12);

        return (new \App\Exports\CrmDashboardExport($data, (string) ($company->name ?? ''), $company->base_currency ?: 'SAR', $who))
            ->download('crm_dashboard_' . $data['asOf']->format('d-m-Y') . '.xlsx');
    }

    /** Everything the page (and the export) shows, for the viewing date and salespeople asked for. */
    private function data(Request $request): array
    {
        $today = CrmActivity::localNowNaive()->startOfDay();
        $asOf = $today;
        if ($request->filled('date')) {
            try {
                $asOf = Carbon::createFromFormat('d-m-Y', $request->input('date'))->startOfDay();
            } catch (\Throwable) {
                $asOf = $today;
            }
        }

        $own = SalesScope::ownSalespersonIds();
        $salespersons = Salesperson::orderBy('name')->when($own, fn($q) => $q->whereIn('id', $own))->get(['id', 'name']);
        $picked = $request->filled('salesperson_id') ? (int) $request->input('salesperson_id') : null;
        // a salesman can narrow to one of his own records, never widen past them
        $scope = $own ? ($picked && in_array($picked, $own, true) ? [$picked] : $own) : ($picked ? [$picked] : null);

        $dash = new CrmDashboard($asOf, $scope);

        return [
            'asOf' => $asOf,
            'isToday' => $asOf->equalTo($today),
            'own' => $own,
            'salespersons' => $salespersons,
            'picked' => $picked,
            'periods' => $dash->periods(),
            'glance' => $dash->atAGlance(),
            'snapshot' => $dash->activitySnapshot(),
            'byStatus' => $dash->pipelineBy('status'),
            'byRank' => $dash->pipelineBy('rank'),
            'byHandler' => $dash->pipelineBy('handled_by'),
            'byService' => $dash->pipelineBy('service'),
            'conversion' => $dash->conversion(),
            'dash' => $dash,
        ];
    }
}
