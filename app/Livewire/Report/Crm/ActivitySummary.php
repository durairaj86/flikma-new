<?php

namespace App\Livewire\Report\Crm;

use App\Models\Crm\CrmAction;
use App\Models\Crm\CrmActivity;
use App\Services\Crm\CrmSettings;
use App\Services\Crm\CustomerTracker;
use App\Services\Crm\SalesScope;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * The sales workbook's Daily / Weekly / Monthly / Quarterly / Annual sheets: how many of every action per period. Columns are
 * all entries, planned entries that are overdue, then each category ("Show each action": each action too). A planned action
 * counts on its due date. Activities are those of the Customer Tracker's lines (a salesman sees his own).
 */
class ActivitySummary extends CrmReport
{
    /** Also one column per action (the workbook's action columns). */
    public $detail = false;

    protected function title(): string { return __('Activity Summary'); }
    protected function subtitle(): string { return __('How many of every action, day by day, week by week, month by month'); }

    protected function groups(): array
    {
        return ['day' => __('Daily'), 'week' => __('Weekly'), 'month' => __('Monthly'), 'quarter' => __('Quarterly'), 'year' => __('Annual')];
    }

    protected function columns(): array
    {
        $cols = [__('Total entries'), __('Overdue planned')];
        foreach (CrmAction::CATEGORIES as $c) {
            $cols[] = __($c['label']);
        }
        if ($this->detail) {
            foreach ($this->actions() as $a) {
                $cols[] = $a->label();
            }
        }

        return $cols;
    }

    protected function emptyText(): string
    {
        return __('No activity in this period.');
    }

    private function actions()
    {
        return CrmAction::options();
    }

    protected function rows(): array
    {
        $tracker = new CustomerTracker();
        $tracker->rows(SalesScope::ownSalespersonIds());
        $activities = collect($tracker->activities)->flatten(1);

        $from = Carbon::parse($this->startDate)->startOfDay();
        $to = Carbon::parse($this->endDate)->endOfDay();
        $today = CrmActivity::localNowNaive()->startOfDay();
        $categories = array_keys(CrmAction::CATEGORIES);
        $actionIds = $this->detail ? $this->actions()->pluck('id')->all() : [];
        $width = 2 + count($categories) + count($actionIds);

        // every period in the range (days only when something happened)
        $buckets = [];
        if ($this->groupBy !== 'day') {
            foreach (CarbonPeriod::create($this->periodStart($from), $this->step(), $to) as $start) {
                $buckets[$this->key($start)] = [$this->label($start)] + array_fill(1, $width, 0);
            }
        }
        foreach ($activities as $a) {
            $date = Carbon::parse($a->follow_up_at ?? $a->activity_at);
            if ($date->lt($from) || $date->gt($to)) {
                continue;
            }
            $key = $this->key($date);
            $buckets[$key] ??= [$this->label($this->periodStart($date))] + array_fill(1, $width, 0);
            $buckets[$key][1]++;
            if ($a->follow_up_at && !$a->follow_up_done_at && $date->lt($today)) {
                $buckets[$key][2]++;
            }
            $i = array_search($a->category, $categories, true);
            if ($i !== false) {
                $buckets[$key][3 + $i]++;
            }
            $j = array_search($a->action_id, $actionIds);
            if ($j !== false) {
                $buckets[$key][3 + count($categories) + $j]++;
            }
        }
        ksort($buckets);

        return array_values($buckets);
    }

    private function weekStart(): int
    {
        return ['monday' => Carbon::MONDAY, 'saturday' => Carbon::SATURDAY][CrmSettings::get('week_start')] ?? Carbon::SUNDAY;
    }

    private function periodStart(Carbon $d): Carbon
    {
        return match ($this->groupBy) {
            'week' => $d->copy()->startOfWeek($this->weekStart()),
            'month' => $d->copy()->startOfMonth(),
            'quarter' => $d->copy()->firstOfQuarter(),
            'year' => $d->copy()->startOfYear(),
            default => $d->copy()->startOfDay(),
        };
    }

    private function step(): string
    {
        return match ($this->groupBy) {
            'week' => '1 week', 'month' => '1 month', 'quarter' => '3 months', 'year' => '1 year', default => '1 day',
        };
    }

    /** Sortable key of the period a date falls in. */
    private function key(Carbon $d): string
    {
        return $this->periodStart($d)->format('Y-m-d');
    }

    private function label(Carbon $start): string
    {
        return match ($this->groupBy) {
            'week' => __('Week of :d', ['d' => $start->format('d-m-Y')]) . ' – ' . $start->copy()->addDays(6)->format('d-m-Y'),
            'month' => $start->translatedFormat('F Y'),
            'quarter' => 'Q' . $start->quarter . ' ' . $start->year,
            'year' => (string) $start->year,
            default => $start->translatedFormat('d-m-Y (D)'),
        };
    }
}
