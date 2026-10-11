<?php

namespace App\Livewire\Report\Pack;

use App\Exports\ReportTableExport;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

/** Base for the small grouped job reports (shipment volume, sales by salesperson). */
abstract class PackReport extends Component
{
    public $startDate;
    public $endDate;
    public $groupBy = '';

    abstract protected function title(): string;
    abstract protected function subtitle(): string;
    /** [key => label] */
    abstract protected function groups(): array;
    /** Column headings after the group column, and which of them are money/number. */
    abstract protected function columns(): array;
    /** Rows of [group, v1, v2 ...] */
    abstract protected function rows(): array;

    public function mount()
    {
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->groupBy = array_key_first($this->groups());
    }

    protected function jobs()
    {
        return DB::table('jobs')->where('jobs.company_id', companyId())->whereNull('jobs.deleted_at')
            ->where('jobs.status', '!=', 3)
            ->whereBetween('jobs.job_date', [$this->startDate, $this->endDate]);
    }

    /** Add a total row (sums of every numeric column). */
    protected function totals(array $rows): array
    {
        $t = [__('Total')];
        for ($i = 1; $i <= count($this->columns()); $i++) {
            $t[] = array_sum(array_column($rows, $i));
        }

        return $t;
    }

    public function exportExcel()
    {
        $rows = $this->rows();
        $cols = array_merge([$this->groups()[$this->groupBy] ?? __('Group')], $this->columns());

        return Excel::download(new ReportTableExport($rows, $this->totals($rows), $cols, [
            'title' => $this->title(),
            'lines' => [$this->startDate . ' - ' . $this->endDate . ' | ' . ($this->groups()[$this->groupBy] ?? '')],
            'numeric_from' => 2,
        ]), str_replace(' ', '_', strtolower($this->title())) . '.xlsx');
    }

    public function render()
    {
        $rows = $this->rows();

        return view('livewire.report.pack.report', [
            'title' => $this->title(), 'subtitle' => $this->subtitle(), 'groups' => $this->groups(),
            'cols' => $this->columns(), 'rows' => $rows, 'totals' => $this->totals($rows),
        ])->layout('components.layouts.app');
    }
}
