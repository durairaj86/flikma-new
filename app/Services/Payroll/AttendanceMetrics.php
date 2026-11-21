<?php

namespace App\Services\Payroll;

use App\Models\Payroll\Shift;

/**
 * Late arrival, short time and overtime of an attendance day, worked out from the check-in / check-out times and the
 * employee's shift (start, end, grace). All values are whole minutes. Without a shift, or without both times, there is nothing to compare.
 *
 *  late      minutes after the shift start, only when the check-in is later than start + grace
 *  short     required shift length minus the time actually worked (check-in to check-out), never negative;
 *            a check-in within the grace time counts as on time, so the grace minutes are not held against the employee
 *  overtime  minutes worked after the shift end
 *
 * Overnight shifts (end before start) run into the next day. Overtime here is informational: payroll pays the overtime hours
 * saved on the attendance record.
 */
class AttendanceMetrics
{
    /** @return array{late:int, short:int, overtime:int} */
    public static function day(?Shift $shift, ?string $checkIn, ?string $checkOut): array
    {
        $zero = ['late' => 0, 'short' => 0, 'overtime' => 0];
        if (!$shift || !$shift->start_time || !$shift->end_time || !$checkIn) {
            return $zero;
        }

        $start = self::minutes($shift->start_time);
        $end = self::minutes($shift->end_time);
        if ($end <= $start) {
            $end += 1440;
        }
        $in = self::minutes($checkIn);
        if ($in < $start - 720) {
            $in += 1440;   // after midnight on an overnight shift
        }

        $out = ['late' => $in > $start + (int) $shift->grace_minutes ? $in - $start : 0, 'short' => 0, 'overtime' => 0];
        if ($checkOut) {
            $o = self::minutes($checkOut);
            if ($o < $in) {
                $o += 1440;
            }
            $from = ($in > $start && $out['late'] === 0) ? $start : $in;   // arriving within the grace time counts as on time
            $out['short'] = max(0, ($end - $start) - ($o - $from));
            $out['overtime'] = max(0, $o - $end);
        }

        return $out;
    }

    /** Totals over a month's attendance rows (only days marked present count). */
    public static function summarize(?Shift $shift, iterable $rows): array
    {
        $t = ['late_days' => 0, 'late' => 0, 'short' => 0, 'overtime' => 0];
        foreach ($rows as $r) {
            if ($r->status !== 'present') {
                continue;
            }
            $m = self::day($shift, $r->check_in, $r->check_out);
            $t['late_days'] += $m['late'] > 0 ? 1 : 0;
            $t['late'] += $m['late'];
            $t['short'] += $m['short'];
            $t['overtime'] += $m['overtime'];
        }

        return $t;
    }

    public static function format(int $minutes): string
    {
        return sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    private static function minutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', substr($time, 0, 5)));

        return $h * 60 + $m;
    }
}
