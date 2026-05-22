<?php

namespace App\Livewire\Report\Finance;

use App\Models\Supplier\Supplier;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SupplierAging extends Component
{
    public string $asOfDate = '';
    public string|int $supplierId = '';
    public string $search = '';
    public array $suppliers = [];

    const AP_ACCOUNT_ID = 18; // Accounts Payable

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
        $this->loadSuppliers();

        if (count($this->suppliers) > 0) {
            $this->supplierId = $this->suppliers[0]['id'];
        }
    }

    public function loadSuppliers(): void
    {
        $query = Supplier::select('id', 'name_en', 'name_ar', 'row_no', 'email', 'phone')
            ->where('status', 3)
            ->orderBy('name_en');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name_en', 'like', '%' . $this->search . '%')
                    ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                    ->orWhere('row_no', 'like', '%' . $this->search . '%');
            });
        }

        $this->suppliers = $query->get()->toArray();
    }

    public function updatedSearch(): void
    {
        $this->loadSuppliers();
    }

    private function getAgingData(): array
    {
        $empty = [
            'supplier' => null,
            'invoices' => [],
            'summary'  => [
                'current'       => 0,
                'days_1_30'     => 0,
                'days_31_60'    => 0,
                'days_61_90'    => 0,
                'days_91_120'   => 0,
                'days_over_120' => 0,
                'grand_total'   => 0,
            ],
        ];

        if (empty($this->supplierId)) {
            return $empty;
        }

        $supplier = Supplier::select('id', 'name_en', 'name_ar', 'row_no', 'email', 'phone')
            ->find($this->supplierId);

        if (!$supplier) {
            return $empty;
        }

        $invoices = SupplierInvoice::where('supplier_id', $this->supplierId)
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
        $summary = [
            'current'       => 0,
            'days_1_30'     => 0,
            'days_31_60'    => 0,
            'days_61_90'    => 0,
            'days_91_120'   => 0,
            'days_over_120' => 0,
            'grand_total'   => 0,
        ];

        foreach ($invoices as $inv) {
            $dueDate = Carbon::parse($inv->due_at ?? $inv->due_date ?? $inv->invoice_date);
            $balance = (float)$inv->grand_total - (float)($inv->paid_amount ?? 0);

            if ($balance <= 0) {
                continue;
            }

            $daysOverdue = (int)$dueDate->diffInDays($asOfDate, false);

            $buckets = array_fill_keys(['current', 'days_1_30', 'days_31_60', 'days_61_90', 'days_91_120', 'days_over_120'], 0.0);

            if ($daysOverdue <= 0) {
                $buckets['current'] = $balance;
                $summary['current'] += $balance;
            } elseif ($daysOverdue <= 30) {
                $buckets['days_1_30'] = $balance;
                $summary['days_1_30'] += $balance;
            } elseif ($daysOverdue <= 60) {
                $buckets['days_31_60'] = $balance;
                $summary['days_31_60'] += $balance;
            } elseif ($daysOverdue <= 90) {
                $buckets['days_61_90'] = $balance;
                $summary['days_61_90'] += $balance;
            } elseif ($daysOverdue <= 120) {
                $buckets['days_91_120'] = $balance;
                $summary['days_91_120'] += $balance;
            } else {
                $buckets['days_over_120'] = $balance;
                $summary['days_over_120'] += $balance;
            }

            $summary['grand_total'] += $balance;

            $agingRows[] = array_merge([
                'invoice_no'   => $inv->row_no,
                'invoice_date' => Carbon::parse($inv->invoice_date)->format('d M Y'),
                'due_date'     => $dueDate->format('d M Y'),
                'days_overdue' => $daysOverdue,
                'total'        => $balance,
            ], $buckets);
        }

        return [
            'supplier' => $supplier,
            'invoices' => $agingRows,
            'summary'  => $summary,
        ];
    }

    public function render()
    {
        $data = $this->getAgingData();

        return view('livewire.report.finance.supplier-aging', array_merge([
            'suppliers' => $this->suppliers,
        ], $data));
    }
}
