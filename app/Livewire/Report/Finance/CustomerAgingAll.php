<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Livewire\Report\Concerns\BuildsAgingBuckets;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerAgingAll extends Component
{
    use BuildsAgingBuckets;

    public string $asOfDate = '';
    public string $search = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function getAgingData(): array
    {
        $asOfDate = Carbon::parse($this->asOfDate);

        $invoices = CustomerInvoice::with('customer:id,name_en,row_no')
            ->where('status', 3)
            ->where(function ($q) {
                $q->whereRaw('COALESCE(paid_amount, 0) < grand_total');
            })
            ->whereHas('customer', function ($q) {
                if (!empty($this->search)) {
                    $q->where(function ($inner) {
                        $inner->where('name_en', 'like', '%' . $this->search . '%')
                            ->orWhere('row_no', 'like', '%' . $this->search . '%');
                    });
                }
            })
            ->orderBy('invoice_date')
            ->get();

        $byCustomer = [];
        $totals = array_merge($this->emptyAgingBuckets(), ['grand_total' => 0.0]);

        foreach ($invoices as $invoice) {
            $balance = (float)$invoice->grand_total - (float)($invoice->paid_amount ?? 0);

            if ($balance <= 0 || !$invoice->customer) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_at ?? $invoice->due_date ?? $invoice->invoice_date);
            $daysOverdue = (int)$dueDate->diffInDays($asOfDate, false);
            $bucketKey = $this->agingBucketKey($daysOverdue);

            $custId = $invoice->customer_id;
            if (!isset($byCustomer[$custId])) {
                $byCustomer[$custId] = array_merge($this->emptyAgingBuckets(), [
                    'customer_id'   => $custId,
                    'customer_name' => $invoice->customer->name_en,
                    'customer_code' => $invoice->customer->row_no,
                    'total'         => 0.0,
                ]);
            }

            $byCustomer[$custId][$bucketKey] += $balance;
            $byCustomer[$custId]['total'] += $balance;
            $totals[$bucketKey] += $balance;
            $totals['grand_total'] += $balance;
        }

        $customers = array_values($byCustomer);
        usort($customers, fn($a, $b) => strcasecmp($a['customer_name'] ?? '', $b['customer_name'] ?? ''));

        return [
            'customers' => $customers,
            'totals'    => $totals,
        ];
    }

    public function exportExcel()
    {
        $data = $this->getAgingData();
        $bucketDefs = $this->agingBucketDefs();

        $columns = array_merge(
            ['Customer ID', 'Customer Name'],
            array_column($bucketDefs, 'label'),
            ['Total']
        );

        $rows = [];
        foreach ($data['customers'] as $cust) {
            $row = [$cust['customer_code'], $cust['customer_name']];
            foreach ($bucketDefs as $def) {
                $row[] = (float)$cust[$def['key']] ?: '';
            }
            $row[] = (float)$cust['total'];
            $rows[] = $row;
        }

        $totalsRow = ['', 'TOTAL'];
        foreach ($bucketDefs as $def) {
            $totalsRow[] = (float)$data['totals'][$def['key']];
        }
        $totalsRow[] = (float)$data['totals']['grand_total'];

        $meta = [
            'title' => 'CUSTOMER AGING SUMMARY',
            'lines' => [
                'As of: ' . Carbon::parse($this->asOfDate)->format('d M Y')
                    . '  |  Interval: ' . $this->agingInterval . ' days x ' . $this->agingColumns . ' columns',
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 3,
        ];

        return Excel::download(
            new ReportTableExport($rows, $totalsRow, $columns, $meta),
            'CustomerAgingSummary-' . $this->asOfDate . '.xlsx'
        );
    }

    public function render()
    {
        $data = $this->getAgingData();

        return view('livewire.report.finance.customer-aging-summary', array_merge([
            'company'    => authUserCompany(),
            'bucketDefs' => $this->agingBucketDefs(),
        ], $data));
    }
}
