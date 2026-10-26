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
    use \App\Traits\Finance\AgingAsOf;

    use BuildsAgingBuckets;

    public string $asOfDate = '';
    public string|int $customerId = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    /** The shared customer dropdown posts hashed ids; keep the property as the plain id. */
    public function updatedCustomerId($value): void
    {
        $this->customerId = $value ? (string) decodeId((string) $value) : '';
    }

    public function getAgingData(): array
    {
        $asOfDate = Carbon::parse($this->asOfDate);

        $invoices = CustomerInvoice::with('customer:id,name_en,row_no')
            ->where('status', 3)
            ->whereDate('invoice_date', '<=', Carbon::parse($this->asOfDate))
            ->whereHas('customer')
            ->when(!empty($this->customerId), fn ($q) => $q->where('customer_id', $this->customerId))
            ->orderBy('invoice_date')
            ->get();

        $byCustomer = [];
        $totals = array_merge($this->emptyAgingBuckets(), ['grand_total' => 0.0]);

        $settled = $this->settledAsOf('customer', Carbon::parse($this->asOfDate));


        foreach ($invoices as $invoice) {
            $balance = (float)$invoice->grand_total - ($settled[$invoice->id] ?? 0.0);

            if (abs($balance) < 0.005 || !$invoice->customer) {
                continue;
            }

            // a negative balance is customer credit (settled for more than it was worth): it lowers what is owed, not yet due
            $dueDate = Carbon::parse($invoice->due_at ?? $invoice->due_date ?? $invoice->invoice_date);
            $daysOverdue = $balance < 0 ? 0 : (int)$dueDate->diffInDays($asOfDate, false);
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

        // opening balances are owed too (aged from their date), so each row = that customer's statement balance
        $obs = $this->openingBalancesAsOf('customer', $asOfDate, $this->customerId ?: null);
        $names = $obs ? \App\Models\Customer\Customer::whereIn('id', array_keys($obs))->get(['id', 'name_en', 'row_no'])->keyBy('id') : collect();
        foreach ($obs as $custId => $ob) {
            $bucketKey = $this->agingBucketKey((int) Carbon::parse($ob['date'])->diffInDays($asOfDate, false));
            $byCustomer[$custId] ??= array_merge($this->emptyAgingBuckets(), [
                'customer_id' => $custId, 'customer_name' => $names[$custId]->name_en ?? '', 'customer_code' => $names[$custId]->row_no ?? '', 'total' => 0.0,
            ]);
            $byCustomer[$custId][$bucketKey] += $ob['amount'];
            $byCustomer[$custId]['total'] += $ob['amount'];
            $totals[$bucketKey] += $ob['amount'];
            $totals['grand_total'] += $ob['amount'];
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
