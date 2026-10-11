<?php

namespace App\Services\Attendance;

use App\Models\Payroll\AttendancePunch;
use App\Models\Payroll\PayrollRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;

/**
 * Reads a punch sheet (Excel / CSV exported from a machine), validates every row and — once the user
 * confirms — feeds the good rows through PunchService exactly like a live device would.
 */
class PunchImportService
{
    public const MAX_ROWS = 10000;

    public function __construct(private PunchNormalizer $normalizer, private PunchService $service)
    {
    }

    /** @return array{header: array<int,string>, rows: array<int, array<int, mixed>>} */
    public function read(UploadedFile $file): array
    {
        $sheets = Excel::toArray(new \stdClass, $file);
        $all = $sheets[0] ?? [];
        // first non-empty row is the header
        while ($all && !array_filter($all[0], fn ($c) => $c !== null && $c !== '')) {
            array_shift($all);
        }
        if (count($all) < 2) {
            throw new \InvalidArgumentException(__('The file has no data rows. The first row must be the column headings, followed by the punches.'));
        }
        $header = array_map(fn ($h) => trim((string) $h), array_shift($all));
        if (count(array_filter($header)) < 2) {
            throw new \InvalidArgumentException(__('The first row must contain column headings (at least an employee ID column and a date/time column).'));
        }
        $rows = array_values(array_filter($all, fn ($r) => array_filter($r, fn ($c) => $c !== null && trim((string) $c) !== '')));
        if (count($rows) > self::MAX_ROWS) {
            throw new \InvalidArgumentException(__('Too many rows (:n). Import at most :max rows at a time.', ['n' => count($rows), 'max' => self::MAX_ROWS]));
        }

        return ['header' => $header, 'rows' => $rows];
    }

    /** Guess which column is which from the headings. Values are column indexes or null. */
    public function guessMapping(array $header): array
    {
        $find = function (array $needles) use ($header) {
            foreach ($header as $i => $h) {
                $n = strtolower(preg_replace('/[^a-z0-9]/i', '', $h));
                foreach ($needles as $needle) {
                    if ($n === $needle || ($n !== '' && str_contains($n, $needle))) {
                        return $i;
                    }
                }
            }

            return null;
        };

        return [
            'id' => $find(['deviceuserid', 'userid', 'employeeid', 'empid', 'empcode', 'employeecode', 'empno', 'employeeno', 'userno', 'staffno', 'staffid', 'badgeno', 'pin', 'badge', 'cardno', 'personid', 'enrollid', 'acno', 'id']),
            'datetime' => $find(['datetime', 'punchtime', 'checktime', 'timestamp', 'logtime', 'punchdatetime']),
            'date' => $find(['date']),
            'time' => $find(['time']),
            'direction' => $find(['direction', 'punchtype', 'inout', 'state', 'status', 'type']),
            'match' => 'device_user_id',
            'time_format' => 'auto',
        ];
    }

    /**
     * Validate every row.
     *
     * @return array{rows: array<int,array>, counts: array<string,int>, fatal: ?string}
     */
    public function analyse(array $rows, array $map, string $timezone, int $companyId): array
    {
        $counts = ['total' => count($rows), 'ok' => 0, 'error' => 0, 'duplicate' => 0, 'unmapped' => 0, 'locked' => 0];
        $out = [];
        $idCol = $this->col($map['id'] ?? null);
        $dtCol = $this->col($map['datetime'] ?? null);
        $dateCol = $this->col($map['date'] ?? null);
        $timeCol = $this->col($map['time'] ?? null);
        $dirCol = $this->col($map['direction'] ?? null);

        if ($idCol === null) {
            return ['rows' => [], 'counts' => $counts, 'fatal' => __('Choose the column that holds the employee / machine ID.')];
        }
        if ($dtCol === null && ($dateCol === null || $timeCol === null)) {
            return ['rows' => [], 'counts' => $counts, 'fatal' => __('Choose either one "Date & time" column, or both a "Date" column and a "Time" column.')];
        }

        $empCache = [];
        $lockCache = [];
        $seen = [];
        $existing = null;   // lazily loaded set of already-stored punches
        $now = now($timezone);

        foreach ($rows as $n => $r) {
            $line = $n + 2;                       // +1 header, +1 for 1-based
            $rawId = trim((string) ($r[$idCol] ?? ''));
            $rawId = preg_replace('/\.0+$/', '', $rawId);   // Excel turns 17 into 17.0
            $res = ['line' => $line, 'id' => $rawId, 'time' => null, 'direction' => null, 'status' => 'ok', 'message' => '', 'employee' => null];

            if ($rawId === '') {
                $out[] = $this->fail($res, __('Employee / machine ID is empty.'), $counts);
                continue;
            }

            $dt = $this->dateTime($r, $dtCol, $dateCol, $timeCol, $timezone, $map['time_format'] ?? 'auto');
            if (!$dt) {
                $out[] = $this->fail($res, __('Date / time is missing or not understood (:v).', ['v' => $this->preview($r, $dtCol ?? $dateCol)]), $counts);
                continue;
            }
            if ($dt->gt($now->copy()->addDay())) {
                $out[] = $this->fail($res, __('Date is in the future.'), $counts);
                continue;
            }
            if ($dt->lt($now->copy()->subYears(2))) {
                $out[] = $this->fail($res, __('Date is more than 2 years old — check the time format.'), $counts);
                continue;
            }
            $res['time'] = $dt;

            if ($dirCol !== null && trim((string) ($r[$dirCol] ?? '')) !== '') {
                $dir = $this->normalizer->readDirection($r[$dirCol]);
                if ($dir === null && !in_array(strtolower(trim((string) $r[$dirCol])), ['break', 'break out', 'break in', 'ot in', 'ot out'], true)) {
                    $out[] = $this->fail($res, __('Direction ":v" is not recognised (use in / out).', ['v' => $r[$dirCol]]), $counts);
                    continue;
                }
                $res['direction'] = $dir;
            }

            $key = $rawId . '|' . $dt->format('Y-m-d H:i:s');
            if (isset($seen[$key])) {
                $res['status'] = 'duplicate';
                $res['message'] = __('Same person and time appear earlier in this file.');
                $counts['duplicate']++;
                $out[] = $res;
                continue;
            }
            $seen[$key] = true;

            $existing ??= AttendancePunch::withoutGlobalScopes()->where('company_id', $companyId)
                ->whereIn('device_user_id', collect($rows)->map(fn ($x) => preg_replace('/\.0+$/', '', trim((string) ($x[$idCol] ?? ''))))->filter()->unique()->values())
                ->pluck('status', \Illuminate\Support\Facades\DB::raw("concat(device_user_id,'|',punched_at)"))->all();
            if (isset($existing[$key]) && $existing[$key] !== 'unmapped') {
                $res['status'] = 'duplicate';
                $res['message'] = __('Already imported / received before.');
                $counts['duplicate']++;
                $out[] = $res;
                continue;
            }

            $emp = $this->employee($map['match'] ?? 'device_user_id', $rawId, $companyId, $empCache);
            if (!$emp) {
                $res['status'] = 'unmapped';
                $res['message'] = __('No employee has this ID — saved in Punch Log until you link it.');
                $counts['unmapped']++;
                $out[] = $res;
                continue;
            }
            $res['employee'] = $emp->name;

            $lk = $emp->id . '-' . $dt->format('Y-n');
            $lockCache[$lk] ??= PayrollRecord::withoutGlobalScopes()->where('company_id', $companyId)->where('employee_id', $emp->id)
                ->where('status', '!=', 'cancelled')->where('month', (int) $dt->month)->where('year', (int) $dt->year)->exists();
            if ($lockCache[$lk]) {
                $res['status'] = 'locked';
                $res['message'] = __('Payroll for :m already exists — not applied to attendance.', ['m' => $dt->format('M Y')]);
                $counts['locked']++;
                $out[] = $res;
                continue;
            }

            $counts['ok']++;
            $out[] = $res;
        }

        return ['rows' => $out, 'counts' => $counts, 'fatal' => null];
    }

    /** Re-validate and import. Everything except error rows and duplicates goes through PunchService. */
    public function import(array $rows, array $map, string $timezone, int $companyId): array
    {
        $analysis = $this->analyse($rows, $map, $timezone, $companyId);
        if ($analysis['fatal']) {
            throw new \InvalidArgumentException($analysis['fatal']);
        }

        $punches = [];
        foreach ($analysis['rows'] as $r) {
            if (in_array($r['status'], ['ok', 'unmapped', 'locked'], true)) {
                $p = ['device_user_id' => $r['id'], 'punched_at' => $r['time'], 'direction' => $r['direction'], 'raw' => ['import_line' => $r['line']]];
                if (($map['match'] ?? '') === 'employee_id' && ctype_digit($r['id'])) {
                    $p = ['employee_id' => (int) $r['id']] + $p;
                }
                $punches[] = $p;
            }
        }
        $device = \App\Models\Payroll\AttendanceDevice::forImport($companyId, $timezone);

        return ['analysis' => $analysis['counts'], 'result' => $this->service->ingest($device, $punches)];
    }

    private function employee(string $match, string $id, int $companyId, array &$cache)
    {
        $cache[$match . $id] ??= (function () use ($match, $id, $companyId) {
            $base = \App\Models\User::withoutGlobalScopes()->where('company_id', $companyId)->where('is_employee', true)->whereNull('terminated_at');
            return match ($match) {
                'employee_id' => ctype_digit($id) ? $base->find((int) $id) : null,
                'employee_code' => (clone $base)->where('employee_code', $id)->first(),
                default => $this->service->resolveEmployee($companyId, $id),
            } ?: false;
        })();

        return $cache[$match . $id] ?: null;
    }

    private function dateTime(array $r, ?int $dt, ?int $date, ?int $time, string $tz, string $fmt): ?Carbon
    {
        if ($dt !== null) {
            return $this->cellToCarbon($r[$dt] ?? null, $tz, $fmt);
        }
        $d = $r[$date] ?? null;
        $t = $r[$time] ?? null;
        if ($d === null || $d === '' || $t === null || $t === '') {
            return null;
        }
        if (is_numeric($d) && is_numeric($t) && (float) $d > 20000) {
            return $this->cellToCarbon((float) $d + ((float) $t < 1 ? (float) $t : 0), $tz, $fmt);
        }
        $dc = $this->cellToCarbon($d, $tz, 'auto');
        if (!$dc) {
            return null;
        }
        $ts = is_numeric($t) && (float) $t < 1 ? gmdate('H:i:s', (int) round((float) $t * 86400)) : trim((string) $t);

        return $this->cellToCarbon($dc->format('Y-m-d') . ' ' . $ts, $tz, $fmt === 'auto' ? 'auto' : 'auto');
    }

    /** Excel stores dates as serial numbers (e.g. 46306.3347) — convert those, otherwise use the normal parser. */
    private function cellToCarbon($v, string $tz, string $fmt): ?Carbon
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v) && (float) $v > 20000 && (float) $v < 80000 && $fmt !== 'epoch') {
            $d = XlDate::excelToDateTimeObject((float) $v);
            return Carbon::create($d->format('Y'), $d->format('m'), $d->format('d'), $d->format('H'), $d->format('i'), $d->format('s'), $tz)->startOfSecond();
        }

        return $this->normalizer->readTime($v, $tz, $fmt);
    }

    private function fail(array $res, string $msg, array &$counts): array
    {
        $res['status'] = 'error';
        $res['message'] = $msg;
        $counts['error']++;

        return $res;
    }

    private function col($v): ?int
    {
        return ($v === null || $v === '') ? null : (int) $v;
    }

    private function preview(array $r, ?int $col): string
    {
        $v = $col === null ? '' : trim((string) ($r[$col] ?? ''));

        return $v === '' ? __('empty') : mb_substr($v, 0, 30);
    }
}
