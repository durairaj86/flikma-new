<?php

namespace App\Livewire\Report\Crm;

use App\Enums\EnquiryEnum;
use App\Enums\QuotationEnum;
use Illuminate\Support\Facades\DB;

/**
 * Why deals are lost, and who wins / loses them. Decisions are counted by the date they happened (the record's last
 * change). By reason: lost quotations, enquiries and prospects per lost reason ("Expired" when a quotation simply
 * ran out without an answer). By salesperson / customer: won vs lost quotations and their value.
 */
class WinLoss extends CrmReport
{
    protected function title(): string { return __('Win / Loss Analysis'); }
    protected function subtitle(): string { return __('Why deals are lost, and won vs lost by salesperson or customer'); }
    protected function groups(): array { return ['reason' => __('Lost reason'), 'salesperson' => __('Salesperson'), 'customer' => __('Customer')]; }

    protected function columns(): array
    {
        return $this->groupBy === 'reason'
            ? [__('Lost quotations'), __('Lost enquiries'), __('Lost prospects'), __('Lost value'), __('Share %')]
            : [__('Won'), __('Lost'), __('Win rate %'), __('Won value'), __('Lost value')];
    }

    protected function rateColumns(): array
    {
        return $this->groupBy === 'reason'
            ? [5 => fn($t) => 100.0]
            : [3 => fn($t) => self::pct($t[1], $t[1] + $t[2])];
    }

    protected function rows(): array
    {
        [$col, $range] = $this->between('q.updated_at');
        $quotes = $this->base('quotations', 'q')->whereBetween($col, $range)
            ->leftJoin(DB::raw($this->chargesSub() . ' qc'), 'qc.quotation_id', '=', 'q.id');
        $won = [QuotationEnum::ACCEPTED->value, QuotationEnum::CONVERTED->value];
        $lost = [QuotationEnum::CANCELLED->value, QuotationEnum::EXPIRED->value];

        return $this->groupBy === 'reason' ? $this->byReason($quotes, $lost) : $this->byParty($quotes, $won, $lost);
    }

    private function byReason($quotes, array $lost): array
    {
        // a quotation that expired without an answer has no reason of its own
        $key = 'COALESCE(%s.lost_reason_id, -1)';
        $q = $this->grouped((clone $quotes)->whereIn('q.status', $lost), sprintf($key, 'q'), 'COUNT(*)');
        $qv = $this->grouped((clone $quotes)->whereIn('q.status', $lost), sprintf($key, 'q'), 'COALESCE(SUM(qc.amt), 0)');
        [$col, $range] = $this->between('e.updated_at');
        $e = $this->grouped($this->base('enquiries', 'e')->where('e.status', EnquiryEnum::CANCELLED->value)->whereBetween($col, $range), sprintf($key, 'e'), 'COUNT(*)');
        [$col, $range] = $this->between('p.updated_at');
        $p = $this->grouped($this->base('prospects', 'p')->where('p.status', 'lost')->whereBetween($col, $range), sprintf($key, 'p'), 'COUNT(*)');

        $keys = array_unique(array_merge(array_keys($q), array_keys($e), array_keys($p)));
        $names = DB::table('lost_reasons')->whereIn('id', array_filter($keys, fn($k) => (int) $k > 0))->pluck('name', 'id')->all();
        $all = array_sum($q) + array_sum($e) + array_sum($p);

        $rows = [];
        foreach ($keys as $k) {
            $n = (int) ($q[$k] ?? 0) + (int) ($e[$k] ?? 0) + (int) ($p[$k] ?? 0);
            $rows[] = [(int) $k === -1 ? __('Expired / no reason given') : ($names[$k] ?? __('(deleted reason)')),
                (int) ($q[$k] ?? 0), (int) ($e[$k] ?? 0), (int) ($p[$k] ?? 0), round((float) ($qv[$k] ?? 0), 2), self::pct($n, $all)];
        }
        usort($rows, fn($a, $b) => ($b[1] + $b[2] + $b[3]) <=> ($a[1] + $a[2] + $a[3]));

        return $rows;
    }

    private function byParty($quotes, array $won, array $lost): array
    {
        $key = $this->groupSql('q', 'updated_at');
        $w = $this->grouped((clone $quotes)->whereIn('q.status', $won), $key, 'COUNT(*)');
        $wv = $this->grouped((clone $quotes)->whereIn('q.status', $won), $key, 'COALESCE(SUM(qc.amt), 0)');
        $l = $this->grouped((clone $quotes)->whereIn('q.status', $lost), $key, 'COUNT(*)');
        $lv = $this->grouped((clone $quotes)->whereIn('q.status', $lost), $key, 'COALESCE(SUM(qc.amt), 0)');

        $keys = array_unique(array_merge(array_keys($w), array_keys($l)));
        $labels = $this->labels($keys);
        $rows = [];
        foreach ($keys as $k) {
            $wn = (int) ($w[$k] ?? 0);
            $ln = (int) ($l[$k] ?? 0);
            $rows[] = [$labels[$k] ?? (string) $k, $wn, $ln, self::pct($wn, $wn + $ln), round((float) ($wv[$k] ?? 0), 2), round((float) ($lv[$k] ?? 0), 2)];
        }
        usort($rows, fn($a, $b) => ($b[1] + $b[2]) <=> ($a[1] + $a[2]));

        return $rows;
    }
}
