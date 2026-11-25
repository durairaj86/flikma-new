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
            // posted_at is the job's real date; job_date is a legacy column that is mostly empty
            ->whereBetween(DB::raw('DATE(COALESCE(jobs.posted_at, jobs.job_date, jobs.created_at))'), [$this->startDate, $this->endDate]);
    }

    /** Text of the empty table. */
    protected function emptyText(): string
    {
        return __('No jobs in this period.');
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
            'lines' => [\Carbon\Carbon::parse($this->startDate)->format('d-m-Y') . ' - ' . \Carbon\Carbon::parse($this->endDate)->format('d-m-Y') . ' | ' . ($this->groups()[$this->groupBy] ?? '')],
            'numeric_from' => 2,
            // a column of whole numbers only (jobs, counts) stays without decimals, as on screen
            'integer_columns' => collect(range(1, count($this->columns())))
                ->filter(fn($i) => $rows && collect($rows)->every(fn($r) => !is_float($r[$i] ?? 0)))->map(fn($i) => $i + 1)->values()->all(),
        // "Win / Loss Analysis": characters a file name cannot hold become "-" (Arabic titles stay as they are)
        ]), str_replace(' ', '_', mb_strtolower(trim(preg_replace('#[\\\\/:*?"<>|]+#', '-', $this->title())))) . '.xlsx');
    }

    public function render()
    {
        $rows = $this->rows();

        return view('livewire.report.pack.report', [
            'title' => $this->title(), 'subtitle' => $this->subtitle(), 'groups' => $this->groups(),
            'cols' => $this->columns(), 'rows' => $rows, 'totals' => $this->totals($rows), 'emptyText' => $this->emptyText(),
        ])->layout('components.layouts.app');
    }
}
