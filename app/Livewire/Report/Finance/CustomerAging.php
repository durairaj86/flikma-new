<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Livewire\Report\Concerns\BuildsAgingBuckets;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerAging extends Component
{
    use BuildsAgingBuckets;

    public string $asOfDate = '';
    public string|int $customerId = '';
    public string $search = '';
    public array $customers = [];

    const AR_ACCOUNT_ID = 17; // Accounts Receivable

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
        $this->loadCustomers();

        if (count($this->customers) > 0) {
            $this->customerId = $this->customers[0]['id'];
        }
    }

    public function loadCustomers(): void
    {
        $query = Customer::select('id', 'name_en', 'name_ar', 'row_no', 'email', 'phone')
            ->where('status', 3)
            ->orderBy('name_en');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name_en', 'like', '%' . $this->search . '%')
                    ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                    ->orWhere('row_no', 'like', '%' . $this->search . '%');
            });
        }

        $this->customers = $query->get()->toArray();
    }

    public function updatedSearch(): void
    {
        $this->loadCustomers();
    }

    private function getAgingData(): array
    {
        $bucketDefs = $this->agingBucketDefs();

        $empty = [
            'customer' => null,
            'invoices' => [],
            'summary'  => array_merge($this->emptyAgingBuckets(), ['grand_total' => 0.0]),
        ];

        if (empty($this->customerId)) {
            return $empty;
        }

        $customer = Customer::select('id', 'name_en', 'name_ar', 'row_no', 'email', 'phone')
            ->find($this->customerId);

        if (!$customer) {
            return $empty;
        }

        $invoices = CustomerInvoice::where('customer_id', $this->customerId)
            ->where('status', 3)
            ->where(function ($q) {
                $q->whereRaw('COALESCE(paid_amount, 0) < grand_total');
            })
            ->when(!empty($this->search), function ($q) {
                $q->where(function ($inner) {
                    $inner->where('row_no', 'like', '%' . $this->search . '%')
                        ->orWhere('invoice_no', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('invoice_date')
            ->get();

        $asOfDate = Carbon::parse($this->asOfDate);
        $agingRows = [];
        $summary = array_merge($this->emptyAgingBuckets(), ['grand_total' => 0.0]);

        foreach ($invoices as $inv) {
            $dueDate = Carbon::parse($inv->due_at ?? $inv->due_date ?? $inv->invoice_date);
            $balance = (float)$inv->grand_total - (float)($inv->paid_amount ?? 0);

            if ($balance <= 0) {
                continue;
            }

            $daysOverdue = (int)$dueDate->diffInDays($asOfDate, false);

            $buckets = $this->emptyAgingBuckets();
            $bucketKey = $this->agingBucketKey($daysOverdue);
            $buckets[$bucketKey] = $balance;
            $summary[$bucketKey] += $balance;
            $summary['grand_total'] += $balance;

            $agingRows[] = [
                'invoice_no'   => $inv->row_no,
                'invoice_date' => Carbon::parse($inv->invoice_date)->format('d M Y'),
                'due_date'     => $dueDate->format('d M Y'),
                'days_overdue' => $daysOverdue,
                'total'        => $balance,
                'buckets'      => $buckets,
            ];
        }

        return [
            'customer' => $customer,
            'invoices' => $agingRows,
            'summary'  => $summary,
        ];
    }

    public function exportExcel()
    {
        $data = $this->getAgingData();

        if (!$data['customer']) {
            return;
        }

        $bucketDefs = $this->agingBucketDefs();

        $columns = array_merge(
            ['Invoice #', 'Invoice Date', 'Due Date', 'Days Overdue'],
            array_column($bucketDefs, 'label'),
            ['Total']
        );

        $rows = [];
        foreach ($data['invoices'] as $inv) {
            $row = [$inv['invoice_no'], $inv['invoice_date'], $inv['due_date'], $inv['days_overdue']];
            foreach ($bucketDefs as $def) {
                $row[] = (float)$inv['buckets'][$def['key']] ?: '';
            }
            $row[] = (float)$inv['total'];
            $rows[] = $row;
        }

        $totalsRow = ['', '', '', 'TOTAL'];
        foreach ($bucketDefs as $def) {
            $totalsRow[] = (float)$data['summary'][$def['key']];
        }
        $totalsRow[] = (float)$data['summary']['grand_total'];

        $meta = [
            'title' => 'CUSTOMER AGING REPORT',
            'lines' => [
                'Customer: ' . $data['customer']->name_en . ' (' . $data['customer']->row_no . ')',
                'As of: ' . Carbon::parse($this->asOfDate)->format('d M Y')
                    . '  |  Interval: ' . $this->agingInterval . ' days x ' . $this->agingColumns . ' columns',
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 5,
        ];

        $filename = 'CustomerAging-' . $data['customer']->row_no . '-' . $this->asOfDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        $data = $this->getAgingData();

        return view('livewire.report.finance.customer-aging', array_merge([
            'company'    => authUserCompany(),
            'customers'  => $this->customers,
            'bucketDefs' => $this->agingBucketDefs(),
        ], $data));
    }
}
