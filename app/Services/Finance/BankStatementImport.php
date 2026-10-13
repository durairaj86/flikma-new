<?php

namespace App\Services\Finance;

use App\Models\Finance\BankReconciliation\BankReconciliation;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

/**
 * Reads a bank statement file (CSV or Excel) and matches it with the open entries of a reconciliation:
 *   - matched rows are ticked (same direction and amount, nearest date within 15 days, each entry used once)
 *   - rows that are not in the books are returned so they can be posted as entries
 * Columns are found by their header names (date, description, debit / withdrawal, credit / deposit or a single signed amount);
 * nothing is saved by reading the file.
 */
class BankStatementImport
{
    private const DATE = ['date', 'value date', 'txn date', 'transaction date', 'posting date', 'booking date', 'تاريخ'];
    private const DESC = ['description', 'details', 'narration', 'narrative', 'particulars', 'reference', 'remarks', 'transaction details', 'memo', 'البيان'];
    private const OUT = ['debit', 'withdrawal', 'withdrawals', 'paid out', 'money out', 'dr', 'debit amount', 'مدين'];
    private const IN = ['credit', 'deposit', 'deposits', 'paid in', 'money in', 'cr', 'credit amount', 'دائن'];
    private const AMOUNT = ['amount', 'transaction amount', 'signed amount', 'المبلغ'];

    /** @return array<int,array{row:int,date:string,description:string,in:float,out:float}> */
    public function parse(UploadedFile $file): array
    {
        $rows = $this->readRows($file);
        // the header is the first row that names a date column and an amount column
        $headerAt = null;
        $map = [];
        foreach ($rows as $i => $r) {
            $m = $this->mapHeader($r);
            if (isset($m['date']) && (isset($m['out']) || isset($m['in']) || isset($m['amount']))) {
                $headerAt = $i;
                $map = $m;
                break;
            }
        }
        if ($headerAt === null) {
            throw new \RuntimeException(__('Could not find the columns. The first row must name a Date column and Debit / Credit (or Amount) columns.'));
        }

        $out = [];
        foreach (array_slice($rows, $headerAt + 1, null, true) as $i => $r) {
            $date = $this->date($r[$map['date']] ?? null);
            if (!$date) {
                continue;   // totals, blank rows, notes
            }
            $in = isset($map['in']) ? $this->number($r[$map['in']] ?? null) : 0.0;
            $outAmt = isset($map['out']) ? $this->number($r[$map['out']] ?? null) : 0.0;
            if (isset($map['amount']) && !$in && !$outAmt) {
                $a = $this->number($r[$map['amount']] ?? null);
                $a > 0 ? $in = $a : $outAmt = abs($a);
            }
            if (!$in && !$outAmt) {
                continue;
            }
            $out[] = ['row' => $i + 1, 'date' => $date->toDateString(), 'description' => trim((string) ($r[$map['desc'] ?? -1] ?? '')), 'in' => round(abs($in), 2), 'out' => round(abs($outAmt), 2)];
        }
        if (!$out) {
            throw new \RuntimeException(__('No transactions were found in the file.'));
        }

        return $out;
    }

    /**
     * @param  array  $rows  from parse()
     * @return array{matched:int,ticked:int[],unmatched:array}
     */
    public function match(BankReconciliation $rec, array $rows, BankReconciliationService $service): array
    {
        $open = $service->lines($rec)->filter(fn ($l) => !$l->ticked)->values()->all();
        $used = [];
        $matched = 0;
        $ticked = [];
        $unmatched = [];

        foreach ($rows as $r) {
            $isIn = $r['in'] > 0;
            $amount = $isIn ? $r['in'] : $r['out'];
            $best = null;
            $bestGap = PHP_INT_MAX;
            foreach ($open as $k => $l) {
                if (isset($used[$k])) {
                    continue;
                }
                $bookAmount = $isIn ? (float) $l->base_debit : (float) $l->base_credit;   // money in = debit on the bank account
                if (abs($bookAmount - $amount) >= 0.005) {
                    continue;
                }
                $gap = abs(Carbon::parse($l->transaction_date)->diffInDays(Carbon::parse($r['date']), false));
                if ($gap <= 15 && $gap < $bestGap) {
                    $best = $k;
                    $bestGap = $gap;
                }
            }
            if ($best === null) {
                $unmatched[] = $r + ['kind' => $isIn ? 'in' : 'out', 'amount' => $amount];
                continue;
            }
            $used[$best] = true;
            $service->tick($rec, (int) $open[$best]->id, true);
            $ticked[] = (int) $open[$best]->id;
            $matched++;
        }

        return ['matched' => $matched, 'ticked' => $ticked, 'unmatched' => $unmatched];
    }

    // ───────────────────────── reading ─────────────────────────

    private function readRows(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['csv', 'txt'], true)) {
            $text = (string) file_get_contents($file->getRealPath());
            $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
            $first = strtok($text, "\n") ?: '';
            $delim = collect([',', ';', "\t", '|'])->sortByDesc(fn ($d) => substr_count($first, $d))->first();
            $rows = [];
            foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
                if (trim($line) !== '') {
                    $rows[] = str_getcsv($line, $delim, '"', '\\');
                }
            }

            return $rows;
        }
        if (!in_array($ext, ['xlsx', 'xls'], true)) {
            throw new \RuntimeException(__('Upload a .csv, .xlsx or .xls file.'));
        }

        return \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
    }

    private function mapHeader(array $row): array
    {
        $map = [];
        foreach ($row as $i => $cell) {
            $h = mb_strtolower(trim((string) $cell));
            if ($h === '') {
                continue;
            }
            foreach (['date' => self::DATE, 'desc' => self::DESC, 'out' => self::OUT, 'in' => self::IN, 'amount' => self::AMOUNT] as $key => $names) {
                if (!isset($map[$key]) && in_array($h, $names, true)) {
                    $map[$key] = $i;
                }
            }
        }

        return $map;
    }

    private function date($v): ?Carbon
    {
        if ($v === null || $v === '') {
            return null;
        }
        try {
            if (is_numeric($v) && $v > 20000 && $v < 80000) {   // Excel serial date
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $v));
            }
            $s = trim((string) $v);
            if (!preg_match('/\d/', $s)) {
                return null;
            }
            if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})/', $s, $m)) {   // day first, as everywhere in this app
                return Carbon::createFromDate((int) ($m[3] < 100 ? 2000 + $m[3] : $m[3]), (int) $m[2], (int) $m[1])->startOfDay();
            }

            return Carbon::parse($s)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function number($v): float
    {
        if ($v === null || $v === '') {
            return 0.0;
        }
        if (is_numeric($v)) {
            return (float) $v;
        }
        $s = trim((string) $v);
        $neg = str_starts_with($s, '(') || str_contains($s, '-') || preg_match('/\bDR\b/i', $s);
        $s = preg_replace('/[^\d.,]/', '', $s);
        if (substr_count($s, ',') && substr_count($s, '.') === 0 && preg_match('/,\d{1,2}$/', $s)) {
            $s = str_replace(',', '.', $s);   // 1234,50
        }
        $n = (float) str_replace(',', '', $s);

        return $neg ? -$n : $n;
    }
}
