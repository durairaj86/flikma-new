<?php

namespace App\Livewire\Report\Concerns;

/**
 * Configurable aging buckets for receivable/payable aging reports.
 *
 * The report is split into: Current (not yet due), N interval buckets of
 * $agingInterval days each, and an "Over X Days" bucket for the remainder.
 * e.g. interval 30 / 4 columns => Current, 1-30, 31-60, 61-90, 91-120, Over 120.
 */
trait BuildsAgingBuckets
{
    public int $agingInterval = 30;
    public int $agingColumns = 4;

    public const AGING_INTERVALS = [30, 45, 60, 90];
    public const AGING_COLUMN_CHOICES = [1, 2, 3, 4];

    public function updatedAgingInterval($value): void
    {
        if (!in_array((int)$value, self::AGING_INTERVALS, true)) {
            $this->agingInterval = 30;
        } else {
            $this->agingInterval = (int)$value;
        }
    }

    public function updatedAgingColumns($value): void
    {
        if (!in_array((int)$value, self::AGING_COLUMN_CHOICES, true)) {
            $this->agingColumns = 4;
        } else {
            $this->agingColumns = (int)$value;
        }
    }

    /**
     * @return array<int, array{key: string, label: string, short: string}>
     */
    public function agingBucketDefs(): array
    {
        $defs = [['key' => 'current', 'label' => 'Current', 'short' => 'Current']];

        for ($i = 1; $i <= $this->agingColumns; $i++) {
            $from = ($i - 1) * $this->agingInterval + 1;
            $to = $i * $this->agingInterval;
            $defs[] = ['key' => "b$i", 'label' => "$from-$to Days", 'short' => "$from-$to"];
        }

        $over = $this->agingColumns * $this->agingInterval;
        $defs[] = ['key' => 'over', 'label' => "Over $over Days", 'short' => ">$over"];

        return $defs;
    }

    public function agingBucketKey(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) {
            return 'current';
        }

        $index = (int)ceil($daysOverdue / $this->agingInterval);

        return $index <= $this->agingColumns ? "b$index" : 'over';
    }

    public function emptyAgingBuckets(): array
    {
        return array_fill_keys(array_column($this->agingBucketDefs(), 'key'), 0.0);
    }
}
