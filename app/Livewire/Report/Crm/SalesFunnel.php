<?php

namespace App\Livewire\Report\Crm;

use App\Enums\EnquiryEnum;
use App\Enums\QuotationEnum;

/**
 * Enquiry → quotation → won → job, for the period. Enquiries and quotations are counted by the date they were made;
 * won / lost are the decisions on those quotations (a cohort), so the rates compare like with like.
 * Versions replaced by a revision are not counted (the chain counts once, as its latest version).
 */
class SalesFunnel extends CrmReport
{
    protected function title(): string { return __('Sales Funnel'); }
    protected function subtitle(): string { return __('Enquiries, quotations, won deals and jobs, with conversion rates'); }
    protected function groups(): array { return ['salesperson' => __('Salesperson'), 'customer' => __('Customer'), 'month' => __('Month')]; }

    protected function columns(): array
    {
        return [__('Enquiries'), __('Quoted %'), __('Quotations'), __('Won'), __('Lost'), __('Win rate %'), __('Jobs')];
    }

    protected function rateColumns(): array
    {
        return [
            2 => fn($t) => $t[1] ? self::pct($this->quotedEnquiries, $t[1]) : 0.0,
            6 => fn($t) => self::pct($t[4], $t[4] + $t[5]),
        ];
    }

    private float $quotedEnquiries = 0;

    protected function rows(): array
    {
        [$col, $range] = $this->between('e.created_at');
        $enq = $this->base('enquiries', 'e')->whereBetween($col, $range);
        $enquiries = $this->grouped(clone $enq, $this->groupSql('e', 'created_at'), 'COUNT(*)');
        $quoted = $this->grouped((clone $enq)->whereIn('e.status', [EnquiryEnum::QUOTATION->value, EnquiryEnum::COMPLETED->value]),
            $this->groupSql('e', 'created_at'), 'COUNT(*)');

        [$col, $range] = $this->between('q.posted_at');
        $qt = $this->base('quotations', 'q')->whereBetween($col, $range)->where('q.status', '!=', QuotationEnum::REVISED->value);
        $qKey = $this->groupSql('q', 'posted_at');
        $quotations = $this->grouped(clone $qt, $qKey, 'COUNT(*)');
        $won = $this->grouped((clone $qt)->whereIn('q.status', [QuotationEnum::ACCEPTED->value, QuotationEnum::CONVERTED->value]), $qKey, 'COUNT(*)');
        $lost = $this->grouped((clone $qt)->whereIn('q.status', [QuotationEnum::CANCELLED->value, QuotationEnum::EXPIRED->value]), $qKey, 'COUNT(*)');

        // the same job date as the other job reports (PackReport::jobs)
        [$col, $range] = $this->between('COALESCE(j.posted_at, j.job_date, j.created_at)');
        $jobs = $this->grouped($this->base('jobs', 'j')->where('j.status', '!=', 3)->whereBetween($col, $range),
            $this->groupSql('j', 'COALESCE(j.posted_at, j.job_date, j.created_at)'), 'COUNT(*)');

        $keys = array_unique(array_merge(array_keys($enquiries), array_keys($quotations), array_keys($jobs)));
        $labels = $this->labels($keys);
        $this->quotedEnquiries = array_sum($quoted);

        if ($this->groupBy === 'month') {
            sort($keys);
        }
        $rows = [];
        foreach ($keys as $k) {
            $e = (int) ($enquiries[$k] ?? 0);
            $w = (int) ($won[$k] ?? 0);
            $l = (int) ($lost[$k] ?? 0);
            $rows[] = [$labels[$k] ?? (string) $k, $e, self::pct($quoted[$k] ?? 0, $e), (int) ($quotations[$k] ?? 0), $w, $l, self::pct($w, $w + $l), (int) ($jobs[$k] ?? 0)];
        }
        if ($this->groupBy !== 'month') {
            usort($rows, fn($a, $b) => $b[3] <=> $a[3] ?: $b[1] <=> $a[1]);
        }

        return $rows;
    }
}
