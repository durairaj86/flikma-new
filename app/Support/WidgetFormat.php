<?php

namespace App\Support;

class WidgetFormat
{
    /** 1234 => 1.23K, 1250000 => 1.25M */
    public static function short($n): string
    {
        $a = abs($n);
        if ($a >= 1000000) return number_format($n / 1000000, 2) . 'M';
        if ($a >= 1000) return number_format($n / 1000, 2) . 'K';
        return number_format($n, 2);
    }

    /** Green/red change pill HTML. $invert=true makes a decrease the good outcome. */
    public static function pill(float $v, bool $invert = false): string
    {
        $good = $invert ? $v <= 0 : $v >= 0;
        return '<span class="kpi-pill ' . ($good ? 'up' : 'down') . '">' . ($v >= 0 ? '&#9650;' : '&#9660;') . ' ' . number_format(abs($v), 1) . '%</span>';
    }
}
