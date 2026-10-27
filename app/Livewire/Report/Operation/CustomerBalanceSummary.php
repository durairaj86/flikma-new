<?php

namespace App\Livewire\Report\Operation;

use App\Exports\ReportTableExport;
use App\Models\Customer\Customer;
use App\Services\Finance\LedgerReports;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerBalanceSummary extends Component
{
    public $startDate;
    public $endDate;
    public $customerId = '';
    public $customers = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
        $this->loadCustomers();
    }

    public function loadCustomers()
    {
        $this->customers = Customer::where('company_id', auth()->user()->company_id ?? 1)
            ->orderBy('name_en')
            ->select('id', 'row_no', 'name_en')
            ->get()
            ->toArray();
    }

    public function applyFilter()
    {
        // Triggers re-render
    }

    /** The shared customer dropdown posts hashed ids; keep the property as the plain id. */
    public function updatedCustomerId($value)
    {
        $this->customerId = $value ? (string) decodeId($value) : '';
    }

    public function resetFilter()
    {
        $this->startDate  = now()->startOfMonth()->format('Y-m-d');
        $this->endDate    = now()->endOfMonth()->format('Y-m-d');
        $this->customerId = '';
        $this->dispatch('cbs-filter-reset');
    }

    protected function getReportData(): array
    {
        $companyId = auth()->user()->company_id ?? 1;

        $customers = Customer::where('company_id', $companyId);

        if (!empty($this->customerId)) {
            $customers->where('id', $this->customerId);
        }

        $customers = $customers->orderBy('name_en')->get();

        $rows   = [];
        $totals = [
            'opening' => 0.0,
            'invoiced' => 0.0,
            'received' => 0.0,
            'closing' => 0.0,
            'overdue' => 0.0,
        ];

        // From the books, in the company currency: each customer's receivable (1130) lines. Debits (invoices, opening
        // balances) raise what the customer owes, credits (receipts, credit notes) lower it. Drafts and cancelled
        // documents are not in the ledger, and an invoice in another currency counts at its booked company-currency
        // amount instead of being added up with the other currencies' own numbers.
        $start = $this->startDate;
        $end   = $this->endDate;
        $sums = DB::table('finance_ledgers')
            ->where('company_id', $companyId)
            ->where('account_code', LedgerReports::RECEIVABLE)
            ->where('is_provisional', 0)
            ->whereNotNull('customer_id')
            ->where('transaction_date', '<=', $end)
            ->when(!empty($this->customerId), fn ($q) => $q->where('customer_id', $this->customerId))
            ->groupBy('customer_id')
            ->selectRaw('customer_id,
                SUM(CASE WHEN transaction_date < ? THEN base_debit - base_credit ELSE 0 END) AS opening,
                SUM(CASE WHEN transaction_date >= ? THEN base_debit ELSE 0 END) AS invoiced,
                SUM(CASE WHEN transaction_date >= ? THEN base_credit ELSE 0 END) AS received', [$start, $start, $start])
            ->get()
            ->keyBy('customer_id');

        // Overall overdue: what is still unpaid today on approved invoices past their due date (receipts and credit notes
        // taken off, as the aging reports do), in the company currency. Not scoped to the period: it is today's exposure.
        $settled = LedgerReports::settledAsOf('customer', now());
        $overdueBy = [];
        CustomerInvoice::where('company_id', $companyId)
            ->where('status', \App\Enums\CustomerInvoiceEnum::APPROVED->value)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now()->startOfDay())
            ->when(!empty($this->customerId), fn ($q) => $q->where('customer_id', $this->customerId))
            ->get(['id', 'customer_id', 'grand_total', 'base_grand_total'])
            ->each(function ($inv) use ($settled, &$overdueBy) {
                $open = (float) $inv->grand_total - ($settled[$inv->id] ?? 0.0);
                if ($open > 0.01 && (float) $inv->grand_total > 0) {
                    $overdueBy[$inv->customer_id] = ($overdueBy[$inv->customer_id] ?? 0.0) + $open * ((float) $inv->base_grand_total / (float) $inv->grand_total);
                }
            });

        foreach ($customers as $customer) {
            $m = $sums->get($customer->id);
            $opening  = round((float) ($m->opening ?? 0), 2);
            $invoiced = round((float) ($m->invoiced ?? 0), 2);
            $received = round((float) ($m->received ?? 0), 2);
            $closing  = $opening + $invoiced - $received;

            // Skip customers with no activity in this period and no carried-forward balance.
            if ($opening == 0.0 && $invoiced == 0.0 && $received == 0.0) {
                continue;
            }

            $overdue = round($overdueBy[$customer->id] ?? 0.0, 2);

            $rows[] = [
                'customer' => $customer,
                'opening'  => $opening,
                'invoiced' => $invoiced,
                'received' => $received,
                'closing'  => $closing,
                'overdue'  => $overdue,
            ];

            $totals['opening']  += $opening;
            $totals['invoiced'] += $invoiced;
            $totals['received'] += $received;
            $totals['closing']  += $closing;
            $totals['overdue']  += $overdue;
        }

        return [$rows, $totals];
    }

    /** The company's own currency: every figure on this report is in it. */
    public function currency(): string
    {
        return strtoupper(optional(authUserCompany())->base_currency ?: 'SAR');
    }

    public function exportExcel()
    {
        [$rows, $totals] = $this->getReportData();

        $columns = ['Customer', 'Code', 'Opening Balance', 'Invoiced', 'Received / Adjusted', 'Closing Balance', 'Overall Overdue Balance'];

        $exportRows = [];
        foreach ($rows as $row) {
            $exportRows[] = [
                $row['customer']->name_en, $row['customer']->row_no,
                (float) $row['opening'], (float) $row['invoiced'], (float) $row['received'], (float) $row['closing'],
                (float) $row['overdue'],
            ];
        }

        $totalsRow = [
            '', 'TOTAL',
            (float) $totals['opening'], (float) $totals['invoiced'], (float) $totals['received'], (float) $totals['closing'],
            (float) $totals['overdue'],
        ];

        $meta = [
            'title' => 'CUSTOMER BALANCE SUMMARY',
            'lines' => [
                'Period: ' . Carbon::parse($this->startDate)->format('d M Y') . ' — ' . Carbon::parse($this->endDate)->format('d M Y'),
                'Currency: ' . $this->currency(),
                'Total Customers: ' . count($rows),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 3,
        ];

        $filename = 'CustomerBalanceSummary-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($exportRows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        [$rows, $totals] = $this->getReportData();

        return view('livewire.report.operation.customer-balance-summary', [
            'rows'   => $rows,
            'totals' => $totals,
            'currency' => $this->currency(),
        ]);
    }
}
