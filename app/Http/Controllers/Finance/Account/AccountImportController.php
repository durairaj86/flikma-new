<?php

namespace App\Http\Controllers\Finance\Account;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Chart-of-accounts import. Two steps so the customer stays in control:
 *  1. preview(): any file (xlsx/xls/csv/pdf/image, any layout) is read by AI, mapped to our
 *     structure (code, name, type, parent) and returned for review — nothing is saved.
 *  2. import(): the reviewed rows are validated again server-side and saved in one transaction.
 */
class AccountImportController extends Controller
{
    private const TYPES = ['Asset', 'Liability', 'Equity', 'Income', 'Expense'];
    private const TYPE_BASE = ['Asset' => 1000, 'Liability' => 2000, 'Equity' => 3000, 'Income' => 4000, 'Expense' => 5000];
    private const CHUNK = 40;

    public function page()
    {
        return view('modules.finance.accounts.import');
    }

    /** A small example file showing the columns we understand. */
    public function sample()
    {
        $rows = [
            ['code', 'name', 'type', 'parent_code'],
            ['1000', 'Assets', 'Asset', ''],
            ['1100', 'Cash and Bank', 'Asset', '1000'],
            ['1110', 'Main Bank Account', 'Asset', '1100'],
            ['2000', 'Liabilities', 'Liability', ''],
            ['2100', 'Accounts Payable', 'Liability', '2000'],
            ['4000', 'Income', 'Income', ''],
            ['4100', 'Freight Revenue', 'Income', '4000'],
            ['5000', 'Expenses', 'Expense', ''],
            ['5100', 'Port Charges', 'Expense', '5000'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, 'chart-of-accounts-sample.csv', ['Content-Type' => 'text/csv']);
    }

    public function preview(Request $request, GeminiService $gemini): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt,pdf,jpg,jpeg,png,webp|max:10240']);
        @set_time_limit(300);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        try {
            if (in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
                $raw = $this->mapSpreadsheet($file->getRealPath(), $gemini);
            } else {
                $raw = $this->mapDocument($file, $gemini);
            }
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Could not read this file. Please check it and try again.'], 422);
        }

        if ($raw === null) {
            return response()->json(['error' => 'AI could not analyse the file right now. Please try again in a moment.'], 500);
        }
        if (!$raw) {
            return response()->json(['error' => 'No accounts were found in this file.'], 422);
        }

        $rows = $this->normalise($raw);

        return response()->json([
            'rows' => $rows,
            'summary' => [
                'total' => count($rows),
                'new' => collect($rows)->where('status', 'new')->count(),
                'skipped' => collect($rows)->where('status', 'exists')->count(),
                'invalid' => collect($rows)->where('status', 'invalid')->count(),
            ],
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rows' => 'required|array|min:1|max:2000',
            'rows.*.name' => 'required|string|max:255',
            'rows.*.code' => 'nullable|string|max:20',
            'rows.*.type' => 'nullable|string|max:20',
            'rows.*.parent_code' => 'nullable|string|max:20',
            'rows.*.parent_name' => 'nullable|string|max:255',
            'rows.*.description' => 'nullable|string|max:255',
            'rows.*.account_number' => 'nullable|string|max:50',
        ]);

        // Re-run the same normalisation on what the customer approved: never trust the browser.
        $rows = collect($this->normalise($data['rows']))->where('status', 'new')->values();
        if ($rows->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => __('Nothing to import.')], 422);
        }

        $created = 0;
        DB::beginTransaction();
        try {
            $byCode = Account::where(function ($q) {
                $q->where('company_id', companyId())->orWhereNull('company_id');
            })->get()->keyBy('code');

            // Parents first, so every child finds its parent already saved.
            foreach ($rows->sortBy(fn ($r) => $r['level'])->values() as $r) {
                $parent = $r['parent_code'] ? ($byCode[$r['parent_code']] ?? null) : null;

                $account = new Account();
                $this->setBaseColumns($account);
                $account->name = $r['name'];
                $account->code = $r['code'];
                $account->type = $parent?->type ?? $r['type'];
                $account->parent_id = $parent?->id;
                $account->is_level = $parent ? $parent->is_level + 1 : 0;
                $account->is_grouped = 0;
                $account->is_last = 1;
                $account->is_active = 1;
                $account->description = $r['description'] ?: null;
                $account->account_number = $r['account_number'] ?: null;
                $account->save();

                if ($parent && !$parent->is_grouped) {
                    $parent->is_grouped = 1;
                    $parent->is_last = 0;
                    $parent->save();
                }

                $byCode[$r['code']] = $account;
                $created++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['status' => 'error', 'message' => __('Import failed, nothing was saved: ') . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success', 'message' => "{$created} accounts imported.", 'created' => $created]);
    }

    // ---------------------------------------------------------------- reading the file

    private function prompt(string $source): string
    {
        $types = implode(', ', self::TYPES);

        return <<<PROMPT
You are mapping a customer's chart of accounts (any layout, any language) into our accounting structure.
Our structure: every account has
- "code": 4-6 digit number (as printed; empty string if the source has none)
- "name": account name
- "type": exactly one of {$types}. Infer from the section heading, a type column, the code's first digit (1 Asset, 2 Liability, 3 Equity, 4 Income, 5 Expense) or the name. Revenue/Sales = Income; Cost of sales, COGS, overheads, operating costs = Expense; Capital/Reserves/Drawings = Equity.
- "parent_code": code of its parent / group account, empty string for a top-level account
- "parent_name": name of its parent / group account if the parent has no code, else empty string
- "description": short note if the source has one, else empty string
- "account_number": bank account number if shown, else empty string

Use the layout clues: parent/group columns, indentation, code prefixes (e.g. 1100 is under 1000), sub-headings.
SKIP: title lines, blank rows, column headers, "Total ..." rows, balances-only rows without an account name.
Return ONLY JSON: {"accounts":[{"code":"","name":"","type":"","parent_code":"","parent_name":"","description":"","account_number":""}]} in the same order as the source.

SOURCE ({$source}):
PROMPT;
    }

    /** Spreadsheet / CSV: read as text lines (keeping column + indentation clues) and map in chunks. */
    private function mapSpreadsheet(string $path, GeminiService $gemini): ?array
    {
        $book = IOFactory::load($path);
        $lines = [];
        foreach ($book->getAllSheets() as $sheet) {
            $title = $sheet->getTitle();
            foreach ($sheet->toArray(null, true, true, false) as $i => $cells) {
                $cells = array_map(fn ($c) => is_scalar($c) ? (string) $c : '', $cells);
                $first = null;
                foreach ($cells as $col => $v) {
                    if (trim($v) !== '') { $first = $col; break; }
                }
                if ($first === null) continue;
                $indent = strlen($cells[$first]) - strlen(ltrim($cells[$first]));
                $text = implode(' | ', array_map('trim', $cells));
                $lines[] = "[{$title} r" . ($i + 1) . " col{$first} indent{$indent}] " . trim(preg_replace('/(\s*\|\s*)+$/', '', $text));
            }
        }
        if (!$lines) return [];
        if (count($lines) > 1500) {
            throw new \RuntimeException('File too large');
        }

        $header = $lines[0];
        $all = [];
        foreach (array_chunk($lines, self::CHUNK) as $n => $chunk) {
            $body = implode("\n", $n === 0 ? $chunk : array_merge(["(first row of the file, for column meaning:) {$header}", '---'], $chunk));
            $response = $gemini->generateResponse($this->prompt('spreadsheet rows') . "\n" . $body, true, 0.1, 'Import Chart of Accounts');
            $decoded = $response ? json_decode($this->sanitize($response), true) : null;
            if (!is_array($decoded)) return null;
            foreach ($decoded['accounts'] ?? [] as $a) {
                if (is_array($a)) $all[] = $a;
            }
        }

        return $all;
    }

    /** PDF / image: the model reads the document directly. */
    private function mapDocument($file, GeminiService $gemini): ?array
    {
        $item = ['type' => 'OBJECT', 'properties' => collect(['code', 'name', 'type', 'parent_code', 'parent_name', 'description', 'account_number'])->mapWithKeys(fn ($k) => [$k => ['type' => 'STRING']])->all(), 'required' => ['code', 'name', 'type', 'parent_code']];
        $schema = ['type' => 'OBJECT', 'properties' => ['accounts' => ['type' => 'ARRAY', 'items' => $item]], 'required' => ['accounts']];

        $response = $gemini->generateVisionResponse($this->prompt('the attached document'), base64_encode(file_get_contents($file->getRealPath())), $file->getMimeType(), true, $schema, 'Import Chart of Accounts');
        $decoded = $response ? json_decode($this->sanitize($response), true) : null;

        return is_array($decoded) ? array_values(array_filter($decoded['accounts'] ?? [], 'is_array')) : null;
    }

    private function sanitize(string $r): string
    {
        $r = trim(preg_replace(['/^```(?:json)?\s*/i', '/\s*```$/'], '', trim($r)));
        $s = strpos($r, '{');
        $e = strrpos($r, '}');

        return ($s !== false && $e !== false) ? substr($r, $s, $e - $s + 1) : $r;
    }

    // ---------------------------------------------------------------- mapping to our structure

    private function normaliseType(?string $t): ?string
    {
        $t = strtolower(trim((string) $t));
        if ($t === '') return null;
        $map = [
            'Asset' => ['asset', 'assets', 'current asset', 'fixed asset', 'bank', 'cash'],
            'Liability' => ['liability', 'liabilities', 'payable', 'current liability', 'loan'],
            'Equity' => ['equity', 'capital', 'reserve', 'reserves', 'owner', 'drawings', 'retained earnings'],
            'Income' => ['income', 'revenue', 'revenues', 'sales', 'other income'],
            'Expense' => ['expense', 'expenses', 'cost', 'costs', 'cogs', 'cost of sales', 'cost of goods sold', 'overhead', 'direct cost'],
        ];
        foreach ($map as $type => $words) {
            if (in_array($t, $words, true)) return $type;
        }
        foreach (self::TYPES as $type) {
            if (str_starts_with($t, strtolower($type))) return $type;
        }

        return null;
    }

    private function cleanCode(?string $code, array &$notes): string
    {
        $digits = preg_replace('/\D+/', '', (string) $code);
        if ($digits === '') return '';
        if (strlen($digits) > 6) {
            $notes[] = 'code too long, new code assigned';
            return '';
        }
        if (strlen($digits) < 4) {
            $padded = str_pad($digits, 4, '0');
            $notes[] = "code {$digits} → {$padded}";
            return $padded;
        }

        return $digits;
    }

    /**
     * Turns raw AI rows into reviewed rows: cleaned code (auto-assigned when missing/clashing),
     * resolved parent and type, and a status: new | exists | invalid.
     */
    private function normalise(array $raw): array
    {
        $db = Account::where(function ($q) {
            $q->where('company_id', companyId())->orWhereNull('company_id');
        })->get(['id', 'code', 'name', 'type', 'is_level']);

        $dbByCode = $db->keyBy('code');
        $dbNames = $db->mapWithKeys(fn ($a) => [strtolower(trim($a->name)) => $a]);
        $used = $db->pluck('code')->filter()->flip()->all();

        $rows = [];
        // Pass 1: clean fields.
        foreach ($raw as $i => $r) {
            $notes = [];
            $name = trim(preg_replace('/\s+/', ' ', (string) ($r['name'] ?? '')));
            $rows[$i] = [
                'name' => $name,
                'code' => $this->cleanCode($r['code'] ?? '', $notes),
                'type' => $this->normaliseType($r['type'] ?? ''),
                'parent_code' => preg_replace('/\D+/', '', (string) ($r['parent_code'] ?? '')),
                'parent_name' => trim((string) ($r['parent_name'] ?? '')),
                'description' => trim((string) ($r['description'] ?? '')),
                'account_number' => trim((string) ($r['account_number'] ?? '')),
                'notes' => $notes,
                'status' => $name === '' ? 'invalid' : 'new',
                'level' => 0,
            ];
            if ($name === '') $rows[$i]['notes'][] = 'no account name';
        }

        // Pass 2: skip accounts that already exist (same code + name, or same name).
        $fileCodeOwner = [];
        foreach ($rows as $i => &$r) {
            if ($r['status'] !== 'new') continue;
            $key = strtolower($r['name']);
            $existing = ($r['code'] !== '' ? $dbByCode[$r['code']] ?? null : null);
            if (($existing && strtolower(trim($existing->name)) === $key) || isset($dbNames[$key])) {
                $r['status'] = 'exists';
                $r['notes'][] = 'already in your chart of accounts';
                continue;
            }
            if ($r['code'] !== '') {
                if (isset($used[$r['code']]) || isset($fileCodeOwner[$r['code']])) {
                    $r['notes'][] = "code {$r['code']} already used, new code assigned";
                    $r['code'] = '';
                } else {
                    $fileCodeOwner[$r['code']] = $i;
                }
            }
        }
        unset($r);

        // Pass 3: parents (by code, else by name — in the file or already in the system).
        $fileByCode = [];
        $fileByName = [];
        foreach ($rows as $i => $r) {
            if ($r['status'] !== 'new') continue;
            if ($r['code'] !== '') $fileByCode[$r['code']] = $i;
            $fileByName[strtolower($r['name'])] = $i;
        }
        foreach ($rows as $i => &$r) {
            if ($r['status'] !== 'new') continue;
            $pc = $r['parent_code'];
            $parentRow = null; $parentDb = null;
            if ($pc !== '' && $pc !== $r['code']) {
                $parentRow = $fileByCode[$pc] ?? null;
                $parentDb = $dbByCode[$pc] ?? null;
            }
            if ($parentRow === null && !$parentDb && $r['parent_name'] !== '') {
                $pn = strtolower($r['parent_name']);
                $parentRow = $fileByName[$pn] ?? null;
                $parentDb = $parentRow === null ? ($dbNames[$pn] ?? null) : null;
            }
            if ($parentRow === $i) $parentRow = null;
            $r['_parent_row'] = $parentRow;
            $r['_parent_db'] = $parentDb;
            if ($parentDb) {
                $r['type'] = $parentDb->type;
                $r['parent_code'] = $parentDb->code;
                $r['parent_name'] = $parentDb->name;
            } elseif ($parentRow !== null) {
                $r['parent_name'] = $rows[$parentRow]['name'];
            } else {
                // Parent named in the file but unknown to us (e.g. "Revenue"): file it under our root of that type.
                $root = ($pc !== '' || $r['parent_name'] !== '') && $r['type']
                    ? $db->first(fn ($a) => $a->type === $r['type'] && (int) $a->is_level === 0 && in_array($a->code, array_map('strval', array_values(self::TYPE_BASE)), true))
                    : null;
                if ($root) {
                    $r['_parent_db'] = $root;
                    $r['type'] = $root->type;
                    $r['parent_code'] = $root->code;
                    $r['parent_name'] = $root->name;
                    $r['notes'][] = "parent not found, placed under {$root->name}";
                } else {
                    if ($pc !== '' || $r['parent_name'] !== '') $r['notes'][] = 'parent not found, added as top-level';
                    $r['parent_code'] = ''; $r['parent_name'] = '';
                }
            }
        }
        unset($r);

        // Pass 4: resolve type/level top-down (parents may appear after children in the file).
        $resolve = function ($i, $depth = 0) use (&$resolve, &$rows) {
            $r = &$rows[$i];
            if (isset($r['_done'])) return;
            $r['_done'] = true;
            $pr = $r['_parent_row'] ?? null;
            if ($pr !== null && $depth < 30) {
                $resolve($pr, $depth + 1);
                $r['type'] = $rows[$pr]['type'] ?? $r['type'];
                $r['level'] = $rows[$pr]['level'] + 1;
            } elseif (!empty($r['_parent_db'])) {
                $r['level'] = $r['_parent_db']->is_level + 1;
            }
        };
        foreach ($rows as $i => $r) {
            if ($r['status'] === 'new') $resolve($i);
        }

        foreach ($rows as $i => &$r) {
            if ($r['status'] !== 'new') continue;
            if (!$r['type'] && $r['code'] !== '') $r['type'] = array_search((int) substr($r['code'], 0, 1) * 1000, self::TYPE_BASE, true) ?: null;
            if (!$r['type']) {
                $r['status'] = 'invalid';
                $r['notes'][] = 'account type could not be determined';
            }
        }
        unset($r);

        // Pass 5: assign codes to rows still without one, parents before children.
        $order = array_keys(array_filter($rows, fn ($r) => $r['status'] === 'new'));
        usort($order, fn ($a, $b) => $rows[$a]['level'] <=> $rows[$b]['level'] ?: $a <=> $b);
        foreach ($order as $i) {
            $r = &$rows[$i];
            if ($r['code'] === '') {
                $pr = $r['_parent_row'] ?? null;
                $parentCode = $pr !== null ? $rows[$pr]['code'] : ($r['_parent_db']->code ?? '');
                $r['code'] = $this->nextCode($r['type'], $parentCode, $used, $rows);
                $r['notes'][] = 'code assigned automatically';
            }
            $used[$r['code']] = true;
            if (($r['_parent_row'] ?? null) !== null) {
                $r['parent_code'] = $rows[$r['_parent_row']]['code'];
            }
        }
        unset($r);

        return array_values(array_map(function ($r) {
            unset($r['_parent_row'], $r['_parent_db'], $r['_done']);
            $r['note'] = implode('; ', array_unique($r['notes']));
            unset($r['notes']);

            return $r;
        }, $rows));
    }

    private function nextCode(string $type, string $parentCode, array $used, array $rows): string
    {
        foreach ($rows as $r) {
            if (!empty($r['code'])) $used[$r['code']] = true;
        }
        if ($parentCode !== '') {
            // Siblings share the parent's significant prefix (1100 -> 11xx): continue after the highest one.
            $step = str_ends_with($parentCode, '0') ? 10 : 1;
            $prefix = rtrim($parentCode, '0') !== '' ? rtrim($parentCode, '0') : $parentCode;
            $max = (int) $parentCode;
            foreach (array_keys($used) as $c) {
                $c = (string) $c;
                if (strlen($c) === strlen($parentCode) && str_starts_with($c, $prefix) && (int) $c > $max) {
                    $max = (int) $c;
                }
            }
            $n = $max + $step;
            while (isset($used[(string) $n])) $n += $step;
            if ($n <= 999999) return (string) $n;
        }
        $n = self::TYPE_BASE[$type] ?? 1000;
        while (isset($used[(string) $n])) $n += 1;

        return (string) $n;
    }
}
