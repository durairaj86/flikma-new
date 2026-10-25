<?php

namespace App\Http\Controllers\Finance\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\SalesCommission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Sales commission (incentive on gross profit by collection timing): screen, print and Excel. */
class SalesCommissionController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $period] = $this->period($request);
        $salespersonId = $request->filled('salesperson') ? (int) $request->input('salesperson') : null;
        $report = SalesCommission::compute($from, $to, $salespersonId);

        if ($request->input('export') === 'xlsx') {
            return $this->excel($report, $from, $to);
        }

        $salespeople = DB::table('sales_persons')->whereNull('deleted_at')->where('company_id', companyId())->orderBy('name')->pluck('name', 'id');

        return view('modules.reports.sales-commission', compact('report', 'from', 'to', 'period', 'salespersonId', 'salespeople'));
    }

    /** Customer wise sales: the invoices behind the commission, with a line per customer (same filters). */
    public function customerWise(Request $request)
    {
        [$from, $to, $period] = $this->period($request);
        $salespersonId = $request->filled('salesperson') ? (int) $request->input('salesperson') : null;
        $report = SalesCommission::compute($from, $to, $salespersonId);
        // one customer (opened from Customer Wise Sales): keep the rates worked out on the whole period, show only their invoices
        $customerId = $request->filled('customer') ? (int) $request->input('customer') : null;
        $customerName = null;
        if ($customerId) {
            $rows = array_values(array_filter($report['rows'], fn ($r) => $r['customer_id'] === $customerId));
            $add = fn ($k) => round(array_sum(array_column($rows, $k)), 2);
            $report['rows'] = $rows;
            $report['summary'] = array_merge($report['summary'], ['invoiced' => $add('invoiced'), 'cost' => $add('cost'), 'income' => $add('income'),
                'incentive' => $add('commission'), 'pending' => round(array_sum(array_map(fn ($r) => $r['tiers']['pending'], $rows)), 2), 'count' => count($rows)]);
            $customerName = DB::table('customers')->where('id', $customerId)->value('name_en');
        }
        if ($request->input('export') === 'xlsx') {
            return $this->excel($report, $from, $to);
        }
        $salespeople = DB::table('sales_persons')->whereNull('deleted_at')->where('company_id', companyId())->orderBy('name')->pluck('name', 'id');

        $view = match (true) {
            $request->is('reports/customer-wise-commission') => 'modules.reports.customer-wise-commission',
            $request->is('reports/invoice-wise-sales') => 'modules.reports.invoice-wise-sales',
            default => 'modules.reports.customer-wise-sales',
        };
        return view($view, compact('report', 'from', 'to', 'period', 'salespersonId', 'salespeople', 'customerId', 'customerName'));
    }

    /** this / last quarter, this year, or custom dates (default: this quarter) */
    private function period(Request $request): array
    {
        $period = $request->input('period', $request->filled('from') ? 'custom' : 'this_quarter');
        $now = Carbon::today();
        return match ($period) {
            'last_quarter' => [$now->copy()->subQuarter()->firstOfQuarter()->toDateString(), $now->copy()->subQuarter()->lastOfQuarter()->toDateString(), $period],
            'this_year' => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString(), $period],
            'custom' => [Carbon::parse($request->input('from', $now->copy()->firstOfQuarter()))->toDateString(),
                Carbon::parse($request->input('to', $now->copy()->lastOfQuarter()))->toDateString(), $period],
            default => [$now->copy()->firstOfQuarter()->toDateString(), $now->copy()->lastOfQuarter()->toDateString(), 'this_quarter'],
        };
    }

    private function excel(array $report, string $from, string $to)
    {
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s = $book->getActiveSheet()->setTitle('Commission');
        $sum = $report['summary'];
        $lines = [
            ['SALES COMMISSION ' . Carbon::parse($from)->format('d-m-Y') . ' to ' . Carbon::parse($to)->format('d-m-Y')],
            [],
            ['Salesperson', 'Scheme', 'Gross profit', 'Target', 'On time', 'Rate', 'Late', 'Rate', 'Collected after late limit', 'Not collected', 'Incentive'],
        ];
        foreach ($report['people'] as $p) {
            $lines[] = [$p['name'], $p['scheme'], $p['income'], $p['target'], $p['on_time'], $p['rate_on_time'] / 100, $p['late'], $p['rate_late'] / 100,
                $p['over'], $p['pending'], $p['incentive']];
        }
        $lines[] = ['Total', '', $sum['income'], $sum['target'], $sum['on_time'], '', $sum['late'], '', $sum['over'], $sum['pending'], $sum['incentive']];
        $lines[] = [];
        $lines[] = ['Invoice', 'Job', 'Activity', 'Customer', 'Salesperson', 'Invoiced', 'Cost', 'Income', 'Profit %', 'Invoice date', 'Due date', 'Collection status',
            'On time', 'Late', 'After late limit', 'Not collected', 'Commission'];
        foreach ($report['rows'] as $r) {
            $lines[] = [$r['invoice'], $r['job'], $r['activity'], $r['customer'], $r['salesperson'], $r['invoiced'], $r['cost'], $r['income'], $r['profit_pct'],
                $r['invoice_date'], $r['due_date'], $r['status'], $r['tiers']['on_time'], $r['tiers']['late'], $r['tiers']['over'], $r['tiers']['pending'], $r['commission']];
        }
        $s->fromArray($lines, null, 'A1');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        foreach (range('A', 'Q') as $col) $s->getColumnDimension($col)->setAutoSize(true);

        $file = tempnam(sys_get_temp_dir(), 'comm') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($file);
        return response()->download($file, 'Sales-Commission-' . $from . '-' . $to . '.xlsx')->deleteFileAfterSend();
    }
}
