<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Livewire\Report\Concerns\BuildsAgingBuckets;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SupplierAgingAll extends Component
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

        $invoices = SupplierInvoice::with('supplier:id,name_en,row_no')
            ->where('status', 3)
            ->where(function ($q) {
                $q->whereRaw('COALESCE(paid_amount, 0) < grand_total');
            })
            ->whereHas('supplier', function ($q) {
                if (!empty($this->search)) {
                    $q->where(function ($inner) {
                        $inner->where('name_en', 'like', '%' . $this->search . '%')
                            ->orWhere('row_no', 'like', '%' . $this->search . '%');
                    });
                }
            })
            ->orderBy('invoice_date')
            ->get();

        $bySupplier = [];
        $totals = array_merge($this->emptyAgingBuckets(), ['grand_total' => 0.0]);

        foreach ($invoices as $invoice) {
            $balance = (float)$invoice->grand_total - (float)($invoice->paid_amount ?? 0);

            if ($balance <= 0 || !$invoice->supplier) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_at ?? $invoice->invoice_date);
            $daysOverdue = (int)$dueDate->diffInDays($asOfDate, false);
            $bucketKey = $this->agingBucketKey($daysOverdue);

            $suppId = $invoice->supplier_id;
            if (!isset($bySupplier[$suppId])) {
                $bySupplier[$suppId] = array_merge($this->emptyAgingBuckets(), [
                    'supplier_id'   => $suppId,
                    'supplier_name' => $invoice->supplier->name_en,
                    'supplier_code' => $invoice->supplier->row_no,
                    'total'         => 0.0,
                ]);
            }

            $bySupplier[$suppId][$bucketKey] += $balance;
            $bySupplier[$suppId]['total'] += $balance;
            $totals[$bucketKey] += $balance;
            $totals['grand_total'] += $balance;
        }

        $suppliers = array_values($bySupplier);
        usort($suppliers, fn($a, $b) => strcasecmp($a['supplier_name'] ?? '', $b['supplier_name'] ?? ''));

        return [
            'suppliers' => $suppliers,
            'totals'    => $totals,
        ];
    }

    public function exportExcel()
    {
        $data = $this->getAgingData();
        $bucketDefs = $this->agingBucketDefs();

        $columns = array_merge(
            ['Supplier ID', 'Supplier Name'],
            array_column($bucketDefs, 'label'),
            ['Total']
        );

        $rows = [];
        foreach ($data['suppliers'] as $supp) {
            $row = [$supp['supplier_code'], $supp['supplier_name']];
            foreach ($bucketDefs as $def) {
                $row[] = (float)$supp[$def['key']] ?: '';
            }
            $row[] = (float)$supp['total'];
            $rows[] = $row;
        }

        $totalsRow = ['', 'TOTAL'];
        foreach ($bucketDefs as $def) {
            $totalsRow[] = (float)$data['totals'][$def['key']];
        }
        $totalsRow[] = (float)$data['totals']['grand_total'];

        $meta = [
            'title' => 'SUPPLIER AGING SUMMARY',
            'lines' => [
                'As of: ' . Carbon::parse($this->asOfDate)->format('d M Y')
                    . '  |  Interval: ' . $this->agingInterval . ' days x ' . $this->agingColumns . ' columns',
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 3,
        ];

        return Excel::download(
            new ReportTableExport($rows, $totalsRow, $columns, $meta),
            'SupplierAgingSummary-' . $this->asOfDate . '.xlsx'
        );
    }

    public function render()
    {
        $data = $this->getAgingData();

        return view('livewire.report.finance.supplier-aging-summary', array_merge([
            'company'    => authUserCompany(),
            'bucketDefs' => $this->agingBucketDefs(),
        ], $data));
    }
}
