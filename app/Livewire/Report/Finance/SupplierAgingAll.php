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
    use \App\Traits\Finance\AgingAsOf;

    use BuildsAgingBuckets;

    public string $asOfDate = '';
    public string|int $supplierId = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    /** The shared supplier dropdown posts hashed ids; keep the property as the plain id. */
    public function updatedSupplierId($value): void
    {
        $this->supplierId = $value ? (string) decodeId((string) $value) : '';
    }

    public function getAgingData(): array
    {
        $asOfDate = Carbon::parse($this->asOfDate);

        $invoices = SupplierInvoice::with('supplier:id,name_en,row_no')
            ->where('status', 3)
            ->whereDate('invoice_date', '<=', Carbon::parse($this->asOfDate))
            ->whereHas('supplier')
            ->when(!empty($this->supplierId), fn ($q) => $q->where('supplier_id', $this->supplierId))
            ->orderBy('invoice_date')
            ->get();

        $bySupplier = [];
        $totals = array_merge($this->emptyAgingBuckets(), ['grand_total' => 0.0]);

        $settled = $this->settledAsOf('supplier', Carbon::parse($this->asOfDate));


        foreach ($invoices as $invoice) {
            $balance = (float)$invoice->grand_total - ($settled[$invoice->id] ?? 0.0);

            if (abs($balance) < 0.005 || !$invoice->supplier) {
                continue;
            }

            // a negative balance (bill settled for more than it was worth) lowers what is owed, not yet due
            $dueDate = Carbon::parse($invoice->due_at ?? $invoice->invoice_date);
            $daysOverdue = $balance < 0 ? 0 : (int)$dueDate->diffInDays($asOfDate, false);
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

        // opening balances are owed too (aged from their date), so each row = that supplier's statement balance
        $obs = $this->openingBalancesAsOf('supplier', $asOfDate, $this->supplierId ?: null);
        $names = $obs ? \App\Models\Supplier\Supplier::whereIn('id', array_keys($obs))->get(['id', 'name_en', 'row_no'])->keyBy('id') : collect();
        foreach ($obs as $suppId => $ob) {
            $bucketKey = $this->agingBucketKey((int) Carbon::parse($ob['date'])->diffInDays($asOfDate, false));
            $bySupplier[$suppId] ??= array_merge($this->emptyAgingBuckets(), [
                'supplier_id' => $suppId, 'supplier_name' => $names[$suppId]->name_en ?? '', 'supplier_code' => $names[$suppId]->row_no ?? '', 'total' => 0.0,
            ]);
            $bySupplier[$suppId][$bucketKey] += $ob['amount'];
            $bySupplier[$suppId]['total'] += $ob['amount'];
            $totals[$bucketKey] += $ob['amount'];
            $totals['grand_total'] += $ob['amount'];
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
