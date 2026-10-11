<?php

namespace App\Services\Attendance;

use Illuminate\Support\Carbon;

/**
 * Accepts the many payload shapes different machines / middleware send and reduces them to one format.
 * Field names are matched loosely (pin, user_id, emp_code …) so most devices work without custom code.
 */
class PunchNormalizer
{
    private const ID_KEYS = ['device_user_id', 'user_id', 'userid', 'pin', 'emp_id', 'empcode', 'emp_code', 'employee_code', 'badge', 'badge_no', 'card_no', 'cardno', 'person_id', 'staff_id', 'enrollid', 'enroll_id', 'id_number'];
    private const TIME_KEYS = ['timestamp', 'punched_at', 'punch_time', 'punchtime', 'datetime', 'date_time', 'time', 'check_time', 'checktime', 'log_time', 'logtime', 'event_time', 'recordtime'];
    private const DIR_KEYS = ['type', 'direction', 'punch_type', 'punchtype', 'state', 'status', 'in_out', 'inout', 'io', 'event', 'punch_state', 'attendance_state'];
    private const LIST_KEYS = ['punches', 'records', 'logs', 'data', 'events', 'items', 'attendancelogs', 'attendance', 'transactions'];

    /**
     * @param  array|null  $map  per-device column mapping (see AttendanceDevice::$field_map). Null/empty = auto-detect.
     * @return array<int, array{device_user_id?:string, employee_id?:int, punched_at:Carbon, direction:?string, raw:array}>
     */
    public function fromPayload(array $payload, string $timezone, ?array $map = null): array
    {
        $map = array_filter($map ?? [], fn ($v) => $v !== null && $v !== '');
        $rows = $this->extractRows($payload, $map['records_path'] ?? null);
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $flat = $this->flatten($row);
            $entry = isset($map['employee_field']) || isset($map['time_field'])
                ? $this->mapped($flat, $map, $timezone)
                : $this->auto($flat, $timezone);
            if ($entry) {
                $out[] = $entry + ['raw' => $row];
            }
        }

        return $out;
    }

    /** Every field of the first record, as dotted keys → sample values (used by the mapping screen). */
    public function sampleFields(array $payload, ?string $recordsPath = null): array
    {
        $rows = $this->extractRows($payload, $recordsPath);
        $first = $rows[0] ?? null;

        return is_array($first) ? $this->flatten($first, false) : [];
    }

    private function mapped(array $flat, array $map, string $timezone): ?array
    {
        $idField = strtolower($map['employee_field'] ?? '');
        $id = $idField !== '' ? ($flat[$idField] ?? null) : $this->firstOf($flat, self::ID_KEYS);
        if ($id === null || $id === '') {
            return null;
        }

        $timeField = strtolower($map['time_field'] ?? '');
        $time = $timeField !== '' ? ($flat[$timeField] ?? null) : $this->firstOf($flat, self::TIME_KEYS);
        $dateField = strtolower($map['date_field'] ?? '');
        if ($dateField !== '' && isset($flat[$dateField]) && $time !== null) {
            $time = trim($flat[$dateField] . ' ' . $time);
        }
        $at = $this->parseTime($time, $timezone, $map['time_format'] ?? 'auto');
        if (!$at) {
            return null;
        }

        $dirField = strtolower($map['direction_field'] ?? '');
        $direction = null;
        if ($dirField !== '' && isset($flat[$dirField])) {
            $v = strtolower(trim((string) $flat[$dirField]));
            $in = array_map('strtolower', array_map('trim', explode(',', (string) ($map['in_values'] ?? ''))));
            $outV = array_map('strtolower', array_map('trim', explode(',', (string) ($map['out_values'] ?? ''))));
            $direction = in_array($v, $in, true) && $v !== '' ? 'in' : (in_array($v, $outV, true) && $v !== '' ? 'out' : $this->direction($v));
        }
        $direction ??= in_array($map['default_direction'] ?? '', ['in', 'out'], true) ? $map['default_direction'] : null;

        $match = $map['employee_match'] ?? 'device_user_id';
        if ($match === 'employee_id') {
            return ctype_digit((string) $id) ? ['employee_id' => (int) $id, 'punched_at' => $at, 'direction' => $direction] : null;
        }

        return ['device_user_id' => (string) $id, 'punched_at' => $at, 'direction' => $direction];
    }

    private function auto(array $flat, string $timezone): ?array
    {
        $raw = $this->firstOf($flat, self::TIME_KEYS);
        if (is_string($raw) && preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', trim($raw)) && !empty($flat['date'])) {
            $raw = $flat['date'] . ' ' . trim($raw);   // date and time sent as separate fields
        }
        $time = $this->parseTime($raw ?? $this->joinDateTime($flat), $timezone);
        if (!$time) {
            return null;
        }

        $entry = ['punched_at' => $time, 'direction' => $this->direction($this->firstOf($flat, self::DIR_KEYS))];
        $id = $this->firstOf($flat, self::ID_KEYS);
        if ($id !== null && $id !== '') {
            $entry['device_user_id'] = (string) $id;
        } elseif (!empty($flat['employee_id'])) {
            $entry['employee_id'] = (int) $flat['employee_id'];
        } else {
            return null;
        }

        return $entry;
    }

    /** Nested record → dotted keys. Keys are lower-cased for matching unless $lower is false (display). */
    private function flatten(array $row, bool $lower = true, string $prefix = '', int $depth = 0): array
    {
        $flat = [];
        foreach ($row as $k => $v) {
            $key = $prefix . ($lower ? strtolower(str_replace([' ', '-'], '_', (string) $k)) : (string) $k);
            if (is_array($v) && $depth < 3) {
                $flat += $this->flatten($v, $lower, $key . '.', $depth + 1);
            } elseif (is_scalar($v) || $v === null) {
                $flat[$key] = $v;
            }
        }

        return $flat;
    }

    /**
     * ZKTeco ADMS ATTLOG body: one punch per line, tab separated — PIN, time, status, verify mode, workcode…
     * Status 0/4 = in, 1/5 = out; 2/3 (break) and others carry no direction.
     */
    public function fromZkText(string $body, string $timezone): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($body)) as $line) {
            $cols = preg_split('/\t+|\s{2,}/', trim($line));
            if (count($cols) < 2 || !($time = $this->parseTime($cols[1], $timezone))) {
                continue;
            }
            $status = $cols[2] ?? null;
            $out[] = [
                'device_user_id' => (string) $cols[0],
                'punched_at' => $time,
                'direction' => in_array($status, ['0', '4'], true) ? 'in' : (in_array($status, ['1', '5'], true) ? 'out' : null),
                'raw' => ['line' => $line],
            ];
        }

        return $out;
    }

    private function extractRows(array $payload, ?string $path = null): array
    {
        if ($path !== null && $path !== '') {
            $node = $payload;
            foreach (explode('.', $path) as $seg) {
                if (!is_array($node) || !array_key_exists($seg, $node)) {
                    return [];
                }
                $node = $node[$seg];
            }

            return is_array($node) ? (array_is_list($node) ? $node : [$node]) : [];
        }

        $lower = array_change_key_case($payload, CASE_LOWER);
        foreach (self::LIST_KEYS as $key) {
            if (isset($lower[$key]) && is_array($lower[$key])) {
                $v = $lower[$key];
                return array_is_list($v) ? $v : [$v];
            }
        }

        return array_is_list($payload) ? $payload : [$payload];
    }

    private function firstOf(array $flat, array $keys)
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $flat) && $flat[$k] !== null && $flat[$k] !== '') {
                return $flat[$k];
            }
        }

        return null;
    }

    private function joinDateTime(array $flat): ?string
    {
        $d = $flat['date'] ?? $flat['punch_date'] ?? null;
        $t = $flat['clock'] ?? $flat['punch_clock'] ?? null;

        return $d && $t ? "$d $t" : null;
    }

    /** Public wrappers used by the Excel import validator. */
    public function readTime($v, string $timezone, string $format = 'auto'): ?Carbon
    {
        return $this->parseTime($v, $timezone, $format);
    }

    public function readDirection($v): ?string
    {
        return $this->direction($v);
    }

    private function direction($v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = strtolower(trim((string) $v));
        return match (true) {
            in_array($s, ['in', 'checkin', 'check-in', 'check_in', 'clockin', 'clock_in', 'entry', 'enter', 'i', '0', 'punch in', 'duty on'], true) => 'in',
            in_array($s, ['out', 'checkout', 'check-out', 'check_out', 'clockout', 'clock_out', 'exit', 'leave', 'o', '1', 'punch out', 'duty off'], true) => 'out',
            default => null,
        };
    }

    /** Wall-clock time in the device's timezone. Epoch seconds and ISO strings with an offset are converted into it. */
    private function parseTime($v, string $timezone, string $format = 'auto'): ?Carbon
    {
        if ($v === null || $v === '') {
            return null;
        }
        try {
            if ($format === 'epoch' && is_numeric($v)) {
                return Carbon::createFromTimestamp((int) ((float) $v > 99999999999 ? $v / 1000 : $v), $timezone)->startOfSecond();
            }
            if ($format !== 'auto' && $format !== '' && $format !== 'epoch') {
                $c = Carbon::createFromFormat($format, trim((string) $v), $timezone);
                return $c ? $c->startOfSecond() : null;
            }
            if (is_numeric($v) && (float) $v > 100000000) {
                $ts = (float) $v > 99999999999 ? ((float) $v) / 1000 : (float) $v;
                return Carbon::createFromTimestamp((int) $ts, $timezone)->setTimezone($timezone)->startOfSecond();
            }
            $s = trim((string) $v);
            if (preg_match('/(Z|[+-]\d{2}:?\d{2})$/', $s)) {
                return Carbon::parse($s)->setTimezone($timezone)->startOfSecond();
            }
            // dd-mm-yyyy / dd/mm/yyyy (device style) vs ISO
            if (preg_match('#^(\d{1,2})[-/](\d{1,2})[-/](\d{4})(.*)$#', $s, $m)) {
                $s = sprintf('%04d-%02d-%02d%s', $m[3], $m[2], $m[1], $m[4]);
            }
            return Carbon::parse($s, $timezone)->startOfSecond();
        } catch (\Throwable) {
            return null;
        }
    }
}
