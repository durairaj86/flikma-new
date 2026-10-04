<?php

namespace App\Livewire\Widgets;

use Carbon\Carbon;
use Livewire\Component;

/**
 * Base for the dashboard KPI widgets. One subclass per module (Sales,
 * Quotation, ...) holds that module's data; the *size* only picks the blade:
 *   resources/views/widgets/{module}/{small|medium|large}.blade.php
 * Each widget owns its month, so changing it re-renders just that card.
 */
abstract class Widget extends Component
{
    public string $size = 'medium';

    public string $month = '';

    /** Folder under resources/views/widgets, e.g. "quotation". */
    abstract protected function module(): string;

    /** Numbers for the selected month (first day passed in). */
    abstract protected function data(Carbon $start): array;

    public function mount(string $size = 'medium', ?string $month = null): void
    {
        $this->size = $size;
        $this->month = $month && array_key_exists($month, $this->months()) ? $month : Carbon::now()->format('Y-m');
    }

    public function setMonth(string $month): void
    {
        if (array_key_exists($month, $this->months())) {
            $this->month = $month;
        }
    }

    /** Last 12 months, newest first: ['2026-10' => 'Oct 2026', ...]. */
    protected function months(): array
    {
        $out = [];
        for ($i = 0; $i < 12; $i++) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $out[$m->format('Y-m')] = $m->format('M Y');
        }
        return $out;
    }

    public function render()
    {
        $start = Carbon::createFromFormat('Y-m-d', $this->month . '-01')->startOfDay();
        $view = 'widgets.' . $this->module() . '.' . $this->size;
        if (! view()->exists($view)) {
            $view = 'widgets.' . $this->module() . '.medium';
        }

        return view($view, [
            'd' => $this->data($start),
            'months' => $this->months(),
            'month' => $this->month,
        ]);
    }
}
