<?php

namespace App\Livewire\Report\Crm;

use App\Enums\QuotationEnum;
use App\Models\Crm\CrmActivity;
use Illuminate\Support\Facades\DB;

/**
 * Open pipeline: pending quotations made in the period, with their value, how long they have been open, and how many
 * nobody is following up (no open follow-up). Group by salesperson / customer, or by how soon they expire / how old they are.
 */
class PipelineValue extends CrmReport
{
    protected function title(): string { return __('Pipeline Value'); }
    protected function subtitle(): string { return __('Open quotations waiting for the customer'); }
    protected function groups(): array
    {
        return ['salesperson' => __('Salesperson'), 'customer' => __('Customer'), 'expiry' => __('Expires in'), 'age' => __('Days open')];
    }

    protected function columns(): array
    {
        return [__('Quotations'), __('Value'), __('Avg. days open'), __('No follow-up')];
    }

    protected function rateColumns(): array
    {
        // average of all open quotations, not the sum of the group averages
        return [3 => fn($t) => $t[1] ? round($this->daysTotal / $t[1], 1) : 0.0];
    }

    private float $daysTotal = 0;

    protected function emptyText(): string
    {
        return __('No open quotations made in this period.');
    }

    protected function rows(): array
    {
        $today = CrmActivity::localNowNaive()->startOfDay()->toDateString();
        [$col, $range] = $this->between('q.posted_at');
        $open = $this->base('quotations', 'q')->where('q.status', QuotationEnum::PENDING->value)->whereBetween($col, $range)
            ->leftJoin(DB::raw($this->chargesSub() . ' qc'), 'qc.quotation_id', '=', 'q.id')
            ->leftJoin(DB::raw("(SELECT DISTINCT subject_id FROM crm_activities WHERE subject_type = 'quotation' AND deleted_at IS NULL
                AND follow_up_at IS NOT NULL AND follow_up_done_at IS NULL) fu"), 'fu.subject_id', '=', 'q.id');

        $key = match ($this->groupBy) {
            'expiry' => "CASE WHEN q.valid_until < '{$today}' THEN 0 WHEN DATEDIFF(q.valid_until, '{$today}') <= 7 THEN 1
                WHEN DATEDIFF(q.valid_until, '{$today}') <= 30 THEN 2 ELSE 3 END",
            'age' => "CASE WHEN DATEDIFF('{$today}', q.posted_at) <= 7 THEN 0 WHEN DATEDIFF('{$today}', q.posted_at) <= 30 THEN 1
                WHEN DATEDIFF('{$today}', q.posted_at) <= 60 THEN 2 ELSE 3 END",
            default => $this->groupSql('q', 'posted_at'),
        };
        $data = $open->selectRaw("{$key} AS k, COUNT(*) n, COALESCE(SUM(qc.amt), 0) v, SUM(DATEDIFF('{$today}', q.posted_at)) d, SUM(fu.subject_id IS NULL) nf")
            ->groupBy(DB::raw($key))->get()->keyBy('k');

        $labels = match ($this->groupBy) {
            'expiry' => [0 => __('Past validity, not yet expired'), 1 => __('Within 7 days'), 2 => __('8 – 30 days'), 3 => __('More than 30 days')],
            'age' => [0 => __('0 – 7 days'), 1 => __('8 – 30 days'), 2 => __('31 – 60 days'), 3 => __('More than 60 days')],
            default => $this->labels($data->keys()->all()),
        };
        $this->daysTotal = (float) $data->sum('d');

        $rows = [];
        foreach ($data as $k => $r) {
            $rows[$k] = [$labels[$k] ?? (string) $k, (int) $r->n, round((float) $r->v, 2), $r->n ? round($r->d / $r->n, 1) : 0.0, (int) $r->nf];
        }
        in_array($this->groupBy, ['expiry', 'age'], true) ? ksort($rows) : uasort($rows, fn($a, $b) => $b[2] <=> $a[2] ?: $b[1] <=> $a[1]);

        return array_values($rows);
    }
}
