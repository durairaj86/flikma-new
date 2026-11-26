<?php

namespace App\Services\Crm;

use App\Enums\JobEnum;
use App\Enums\QuotationEnum;
use App\Models\Crm\CrmAction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The sales workbook's Dashboard sheet ("where do I stand?") for one viewing date and the salespeople in scope:
 * at a glance, activity per category and action for each period, pipeline by status / rank / service, and conversion.
 * Lines and activities come from the Customer Tracker, so the numbers always agree with it.
 */
class CrmDashboard
{
    private Collection $lines;
    private Collection $activities;

    public function __construct(private Carbon $asOf, private ?array $salespersons)
    {
        $tracker = new CustomerTracker();
        $this->lines = $tracker->rows($salespersons);
        $this->activities = collect($tracker->activities)->flatten(1);
    }

    /**
     * The dashboard for today and the signed-in user's salespeople, built once per request: the CRM widgets on the main
     * dashboard all read it (the Customer Tracker behind it is the expensive part).
     */
    public static function forWidgets(): self
    {
        static $shared = [];
        $key = (int) companyId() . ':' . (int) auth()->id();

        return $shared[$key] ??= new self(\App\Models\Crm\CrmActivity::localNowNaive()->startOfDay(), SalesScope::ownSalespersonIds());
    }

    /** The Customer Tracker lines behind the dashboard (open = not lost / inactive). */
    public function lines(bool $openOnly = true): Collection
    {
        return $openOnly ? $this->lines->reject(fn($l) => in_array($l['status'], ['Lost', 'Inactive'], true))->values() : $this->lines;
    }

    /** Period key => [label, from, to] (the workbook's columns; weeks start on the CrmSettings day). */
    public function periods(): array
    {
        $d = $this->asOf->copy()->startOfDay();
        $weekStart = $d->copy()->startOfWeek(CrmSettings::get('week_start') === 'monday' ? Carbon::MONDAY : (CrmSettings::get('week_start') === 'saturday' ? Carbon::SATURDAY : Carbon::SUNDAY));

        return [
            'day' => [__('Viewing day'), $d->copy(), $d->copy()->endOfDay()],
            'yesterday' => [__('Yesterday'), $d->copy()->subDay(), $d->copy()->subDay()->endOfDay()],
            'week' => [__('This week'), $weekStart->copy(), $weekStart->copy()->addDays(6)->endOfDay()],
            'last_week' => [__('Last week'), $weekStart->copy()->subDays(7), $weekStart->copy()->subDay()->endOfDay()],
            'month' => [__('This month'), $d->copy()->startOfMonth(), $d->copy()->endOfMonth()],
            'last_month' => [__('Last month'), $d->copy()->subMonthNoOverflow()->startOfMonth(), $d->copy()->subMonthNoOverflow()->endOfMonth()],
            'quarter' => [__('This quarter'), $d->copy()->firstOfQuarter(), $d->copy()->lastOfQuarter()->endOfDay()],
            'year' => [__('This year'), $d->copy()->startOfYear(), $d->copy()->endOfYear()],
        ];
    }

    /** The date an activity counts on: a planned one on its due date, a done one on the day it happened. */
    private function dateOf($a): Carbon
    {
        return Carbon::parse($a->follow_up_at ?? $a->activity_at);
    }

    public function atAGlance(): array
    {
        $p = $this->periods();
        [, $mFrom, $mTo] = $p['month'];
        $today = $this->asOf->copy()->startOfDay();
        $docs = $this->documentCounts($mFrom, $mTo);
        $open = $this->activities->filter(fn($a) => $a->follow_up_at && !$a->follow_up_done_at);

        return [
            'lines' => $this->lines->reject(fn($l) => in_array($l['status'], ['Lost', 'Inactive'], true))->count(),
            'jobs_running' => DB::table('jobs')->where('company_id', companyId())->whereNull('deleted_at')
                ->where('status', JobEnum::PENDING->value)->when($this->salespersons, fn($q) => $q->whereIn('salesperson_id', $this->salespersons))->count(),
            'rfqs' => $docs['rfqs'],
            'quotes' => $docs['quotes'],
            'confirmed' => $docs['confirmed'],
            'overdue' => $open->filter(fn($a) => Carbon::parse($a->follow_up_at)->lt($today))->count(),
            'next7' => $open->filter(fn($a) => Carbon::parse($a->follow_up_at)->gt($today->copy()->endOfDay())
                && Carbon::parse($a->follow_up_at)->lte($today->copy()->addDays(7)->endOfDay()))->count(),
            'stale' => $this->lines->where('stale', true)->count(),
        ];
    }

    /**
     * Activity count per category and per action for every period: ['categories' => [cat => [period => n]],
     * 'actions' => [cat => [action name => [period => n]]], 'total' => [period => n]].
     */
    public function activitySnapshot(): array
    {
        $periods = $this->periods();
        $out = ['categories' => [], 'actions' => [], 'total' => array_fill_keys(array_keys($periods), 0)];
        foreach (array_keys(CrmAction::CATEGORIES) as $cat) {
            $out['categories'][$cat] = array_fill_keys(array_keys($periods), 0);
        }
        // every active action is listed, even with no entries (the workbook's ACTION DETAIL)
        foreach (CrmAction::options() as $action) {
            $out['actions'][$action->category][$action->label()] = array_fill_keys(array_keys($periods), 0);
        }
        foreach ($this->activities as $a) {
            $date = $this->dateOf($a);
            $name = __($a->name);
            foreach ($periods as $key => [, $from, $to]) {
                if ($date->between($from, $to)) {
                    $out['categories'][$a->category][$key] = ($out['categories'][$a->category][$key] ?? 0) + 1;
                    $out['actions'][$a->category][$name][$key] = ($out['actions'][$a->category][$name][$key] ?? 0) + 1;
                    $out['total'][$key]++;
                }
            }
        }

        return $out;
    }

    /** Lines grouped by a column: count, approx cost, selling, margin and margin %. */
    public function pipelineBy(string $field): array
    {
        $groups = $this->lines->groupBy(fn($l) => $l[$field] ?: '—');
        // the (empty) group is listed last, as the workbook's "(Other / blank)"
        $order = match ($field) {
            'status' => array_merge(CustomerTracker::STATUSES, ['—']),
            'rank' => array_merge(array_keys(CrmSettings::RANKS), ['—']),
            default => $groups->keys()->reject(fn($k) => $k === '—')->sort()->values()->push('—')->all(),
        };
        $rows = [];
        foreach ($order as $key) {
            if (!isset($groups[$key])) {
                continue;
            }
            $g = $groups[$key];
            $selling = (float) $g->sum('selling');
            // margin is known only for lines with both an approx cost and selling; none known = no margin (not 0 %)
            $known = $g->filter(fn($l) => $l['margin'] !== null);
            $margin = $known->isNotEmpty() ? (float) $known->sum('margin') : null;
            $knownSelling = (float) $known->sum('selling');
            $rows[] = [
                'label' => $key === '—' ? __('(Other / blank)') : ($field === 'status' ? __($key) : $key),
                'lines' => $g->count(),
                'touches' => $g->sum('touches'),
                'rfqs' => $g->sum('rfqs'),
                'jobs' => $g->sum('jobs'),
                'cost' => (float) $g->sum('cost'),
                'selling' => $selling,
                'margin' => $margin,
                'known_selling' => $knownSelling,
                'margin_pct' => $margin !== null && $knownSelling ? round($margin * 100 / $knownSelling, 1) : null,
            ];
        }

        return $rows;
    }

    /**
     * Activity per week or month up to the viewing date (the workbook's Weekly / Monthly sheets, which its TRENDS charts read):
     * [['label' =>, 'from' => Carbon, 'to' => Carbon, 'total' =>, 'overdue' =>, 'categories' => [cat => n]], ...], oldest first.
     */
    public function trend(string $unit, int $count): array
    {
        $today = $this->asOf->copy()->startOfDay();
        $weekStart = ['monday' => Carbon::MONDAY, 'saturday' => Carbon::SATURDAY][CrmSettings::get('week_start')] ?? Carbon::SUNDAY;
        $last = $unit === 'week' ? $today->copy()->startOfWeek($weekStart) : $today->copy()->startOfMonth();
        $rows = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $from = $unit === 'week' ? $last->copy()->subWeeks($i) : $last->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $to = $unit === 'week' ? $from->copy()->addDays(6)->endOfDay() : $from->copy()->endOfMonth();
            $rows[$from->format('Y-m-d')] = [
                'label' => $unit === 'week' ? $from->format('d-M') : $from->translatedFormat('M Y'),
                'from' => $from, 'to' => $to, 'total' => 0, 'overdue' => 0,
                'categories' => array_fill_keys(array_keys(CrmAction::CATEGORIES), 0),
            ];
        }
        foreach ($this->activities as $a) {
            $date = $this->dateOf($a);
            foreach ($rows as $k => $r) {
                if ($date->between($r['from'], $r['to'])) {
                    $rows[$k]['total']++;
                    $rows[$k]['categories'][$a->category] = ($rows[$k]['categories'][$a->category] ?? 0) + 1;
                    if ($a->follow_up_at && !$a->follow_up_done_at && $date->lt($today)) {
                        $rows[$k]['overdue']++;
                    }
                    break;
                }
            }
        }

        return array_values($rows);
    }

    /** Margin % of a set of groups: their known margin over the selling of the lines whose margin is known. */
    public static function marginPct(array $rows): ?float
    {
        $sell = array_sum(array_column($rows, 'known_selling'));
        $known = array_filter(array_column($rows, 'margin'), fn($m) => $m !== null);

        return $known && $sell ? round(array_sum($known) * 100 / $sell, 1) : null;
    }

    /** RFQ -> quote -> confirmed per period (from the documents, so history before the CRM counts too). */
    public function conversion(): array
    {
        $out = [];
        foreach (['month', 'last_month', 'quarter', 'year'] as $key) {
            [$label, $from, $to] = $this->periods()[$key];
            $c = $this->documentCounts($from, $to);
            $out[] = $c + [
                'label' => $label,
                'rfq_rate' => $c['rfqs'] ? round($c['confirmed'] * 100 / $c['rfqs'], 1) : null,
                'quote_rate' => $c['quotes'] ? round($c['confirmed'] * 100 / $c['quotes'], 1) : null,
            ];
        }

        return $out;
    }

    /**
     * RFQs = enquiries received, quotes = quotations sent (not counting revisions), confirmed = quotations accepted or turned
     * into a job, by the date of the decision (as on the Win / Loss report).
     */
    private function documentCounts(Carbon $from, Carbon $to): array
    {
        $scope = fn($q) => $q->where('company_id', companyId())->whereNull('deleted_at')
            ->when($this->salespersons, fn($w) => $w->whereIn('salesperson_id', $this->salespersons));

        return [
            'rfqs' => DB::table('enquiries')->where($scope)->whereBetween('created_at', [$from, $to])->count(),
            'quotes' => DB::table('quotations')->where($scope)->whereNull('parent_quotation_id')->whereBetween('created_at', [$from, $to])->count(),
            'confirmed' => DB::table('quotations')->where($scope)
                ->whereIn('status', [QuotationEnum::ACCEPTED->value, QuotationEnum::CONVERTED->value])->whereBetween('updated_at', [$from, $to])->count(),
        ];
    }
}
