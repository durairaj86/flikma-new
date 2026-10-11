<?php

namespace App\Http\Controllers\Finance\OpeningBalance;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Supplier\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Opening balance import, in three steps (nothing is saved until the last one):
 *  1. upload():  read the file, keep its rows server-side under a token, suggest which column is which.
 *  2. check():   apply the customer's column mapping and return every row checked (matched account / customer /
 *                supplier, amounts, problems) with the debit / credit totals.
 *  3. import():  re-run the same check on the server and post ONE balanced "OB" voucher in a transaction.
 */
class OpeningBalanceImportController extends Controller
{
    private const MAX_ROWS = 5000;

    /** Field => header words we recognise when suggesting the mapping. */
    private const SYNONYMS = [
        'name'   => ['name', 'account name', 'account', 'ledger', 'ledger name', 'party', 'particulars', 'customer', 'supplier', 'customer name', 'supplier name', 'description'],
        'code'   => ['code', 'account code', 'acc code', 'a/c code', 'account no', 'account number', 'customer code', 'supplier code'],
        'kind'   => ['type', 'kind', 'category', 'group', 'party type', 'account type'],
        'debit'  => ['debit', 'dr', 'debit amount', 'debits', 'opening debit'],
        'credit' => ['credit', 'cr', 'credit amount', 'credits', 'opening credit'],
        'amount' => ['amount', 'balance', 'opening balance', 'closing balance', 'opening'],
        'drcr'   => ['dr/cr', 'drcr', 'dr cr', 'balance type', 'nature', 'side', 'debit/credit'],
        'notes'  => ['notes', 'note', 'remarks', 'narration', 'reference'],
    ];

    public function page()
    {
        $existing = FinanceSub::where('voucher_type', 'OB')->where('company_id', companyId())->with('finance')->first();
        $lockedDate = $existing?->finance?->posted_at ? \Carbon\Carbon::parse($existing->finance->posted_at)->format('Y-m-d') : null;
        $contra = Account::where('is_last', 1)->orderBy('name')->get(['id', 'code', 'name']);

        return view('modules.finance.opening-balance.import', compact('lockedDate', 'contra'));
    }

    public function sample()
    {
        $rows = [
            ['type', 'code', 'name', 'debit', 'credit'],
            ['Account', '1120', 'Bank Accounts', '50000', ''],
            ['Account', '3100', 'Owner Capital', '', '80000'],
            ['Customer', '', 'ABC Trading Co', '20000', ''],
            ['Supplier', '', 'Global Shipping Lines', '', '15000'],
            ['Account', '1130', 'Accounts Receivable (leave out: use customer rows)', '', ''],
        ];
        array_pop($rows);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, 'opening-balance-sample.csv', ['Content-Type' => 'text/csv']);
    }

    // ----------------------------------------------------------------- step 1

    public function upload(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120']);

        try {
            $path = $request->file('file')->getRealPath();
            $ext = strtolower($request->file('file')->getClientOriginalExtension());
            if (in_array($ext, ['csv', 'txt'], true)) {
                // The automatic delimiter guess can pick a space when there is a title line, so decide it from the whole sample.
                $sample = array_slice(preg_split('/\R/', (string) file_get_contents($path, false, null, 0, 20000)), 0, 15);
                $delim = collect([',', ';', "\t", '|'])->sortByDesc(fn($d) => collect($sample)->sum(fn($l) => substr_count($l, $d)))->first();
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setDelimiter($delim);
                $sheet = $reader->load($path)->getActiveSheet()->toArray(null, true, true, false);
            } else {
                $sheet = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
            }
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => __('Could not read this file. Please check it and try again.')], 422);
        }

        $sheet = array_values(array_filter($sheet, fn($r) => collect($r)->contains(fn($c) => trim((string) $c) !== '')));
        // The header row is the first row with at least two filled cells.
        $headerAt = null;
        foreach ($sheet as $i => $r) {
            if (collect($r)->filter(fn($c) => trim((string) $c) !== '')->count() >= 2) { $headerAt = $i; break; }
        }
        if ($headerAt === null || count($sheet) - $headerAt - 1 < 1) {
            return response()->json(['error' => __('No rows were found in this file.')], 422);
        }
        $headers = array_map(fn($h) => trim((string) $h), $sheet[$headerAt]);
        $data = array_slice($sheet, $headerAt + 1, self::MAX_ROWS);
        $width = count($headers);
        $data = array_map(fn($r) => array_map(fn($c) => is_string($c) ? trim($c) : $c, array_pad(array_slice($r, 0, $width), $width, '')), $data);

        $token = Str::random(40);
        Storage::disk('local')->put("ob-imports/$token.json", json_encode(['company_id' => companyId(), 'user_id' => auth()->id(), 'headers' => $headers, 'rows' => $data]));

        return response()->json([
            'token' => $token, 'headers' => $headers, 'total' => count($data),
            'sample' => array_slice($data, 0, 4), 'suggest' => $this->suggest($headers),
        ]);
    }

    /** field => column index, from the header words. */
    private function suggest(array $headers): array
    {
        $map = [];
        $used = [];
        foreach (self::SYNONYMS as $field => $words) {
            foreach ($headers as $i => $h) {
                if (isset($used[$i])) continue;
                if (in_array(mb_strtolower(trim($h)), $words, true)) { $map[$field] = $i; $used[$i] = true; break; }
            }
        }

        return $map;
    }

    // ----------------------------------------------------------------- steps 2 and 3

    public function check(Request $request): JsonResponse
    {
        $res = $this->evaluate($request);
        if (isset($res['error'])) {
            return response()->json(['error' => $res['error']], 422);
        }

        return response()->json($res);
    }

    public function import(Request $request): JsonResponse
    {
        $res = $this->evaluate($request);
        if (isset($res['error'])) {
            return response()->json(['status' => 'error', 'message' => $res['error']], 422);
        }
        $rows = collect($res['rows'])->where('status', 'new')->whereIn('row', $request->input('selected', collect($res['rows'])->where('status', 'new')->pluck('row')->all()))->values();
        if ($rows->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => __('Nothing to import.')], 422);
        }
        $debit = round($rows->sum('debit'), 2);
        $credit = round($rows->sum('credit'), 2);
        $diff = round($debit - $credit, 2);
        $contraId = (int) $request->input('balancing_account_id');
        if ($diff !== 0.0 && !$contraId) {
            return response()->json(['status' => 'error', 'message' => __('Opening balances must balance. Debit :d, credit :c. Choose a balancing account for the difference of :x.', ['d' => number_format($debit, 2), 'c' => number_format($credit, 2), 'x' => number_format(abs($diff), 2)])], 422);
        }
        $date = $res['date'];
        $this->assertPeriodOpen($date, 'date');

        DB::beginTransaction();
        try {
            $svc = app(\App\Services\Finance\OpeningBalanceService::class);
            $lines = $rows->map(fn($r) => ['account_id' => $r['account_id'], 'customer_id' => $r['customer_id'], 'supplier_id' => $r['supplier_id'], 'debit' => $r['debit'], 'credit' => $r['credit'], 'description' => $r['notes'] ?: null])->all();
            if ($diff !== 0.0) {
                // The difference is carried by the automatic balancing line, the same one edits and deletes keep in step.
                $lines[] = ['account_id' => $contraId, 'customer_id' => null, 'supplier_id' => null, 'debit' => $diff < 0 ? abs($diff) : 0, 'credit' => $diff > 0 ? $diff : 0, 'description' => \App\Services\Finance\OpeningBalanceService::BALANCING_NOTE, 'auto' => true];
            }
            $svc->post($date, $lines, 'Opening Balance (import)');
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['status' => 'error', 'message' => __('Import failed, nothing was saved: ') . $e->getMessage()], 500);
        }
        Storage::disk('local')->delete('ob-imports/' . $request->input('token') . '.json');

        return response()->json(['status' => 'success', 'message' => count($lines) . ' ' . __('opening balance lines imported.'), 'created' => count($lines), 'url' => route('finance.opening-balance')]);
    }

    // ----------------------------------------------------------------- the check itself

    private function load(?string $token): ?array
    {
        if (!$token || !preg_match('/^[A-Za-z0-9]{40}$/', $token) || !Storage::disk('local')->exists("ob-imports/$token.json")) {
            return null;
        }
        $d = json_decode(Storage::disk('local')->get("ob-imports/$token.json"), true);

        return ($d && (int) $d['company_id'] === (int) companyId()) ? $d : null; // a token only works for the company that uploaded it
    }

    private function amount($v): ?float
    {
        $s = trim((string) $v);
        if ($s === '' || $s === '-') return 0.0;
        $neg = str_starts_with($s, '(') && str_ends_with($s, ')') || str_starts_with($s, '-');
        $s = preg_replace('/[^0-9.,]/', '', $s);
        // 1,234.56 or 1.234,56
        if (preg_match('/,\d{1,2}$/', $s) && !str_contains($s, '.')) $s = str_replace(',', '.', $s);
        $s = str_replace(',', '', $s);
        if ($s === '' || !is_numeric($s)) return null;

        return $neg ? -(float) $s : (float) $s;
    }

    private function evaluate(Request $request): array
    {
        $data = $this->load($request->input('token'));
        if (!$data) {
            return ['error' => __('This upload has expired. Please upload the file again.')];
        }
        $map = array_map(fn($v) => ($v === null || $v === '') ? null : (int) $v, (array) $request->input('map', []));
        if (!isset($map['name']) && !isset($map['code'])) {
            return ['error' => __('Choose which column holds the account, customer or supplier name (or code).')];
        }
        if (!isset($map['debit']) && !isset($map['credit']) && !isset($map['amount'])) {
            return ['error' => __('Choose the Debit and Credit columns, or one Amount column.')];
        }
        $default = in_array($request->input('kind'), ['auto', 'account', 'customer', 'supplier'], true) ? $request->input('kind') : 'auto';

        $existingOb = FinanceSub::where('voucher_type', 'OB')->where('company_id', companyId())->with('finance')->get();
        $locked = $existingOb->first()?->finance?->posted_at;
        $date = $locked ? \Carbon\Carbon::parse($locked)->format('Y-m-d') : $request->input('date');
        if (!$date || !strtotime($date)) {
            return ['error' => __('Choose the opening balance date.')];
        }
        $date = \Carbon\Carbon::parse($date)->format('Y-m-d');

        // Lookups, once.
        $accounts = Account::where('is_last', 1)->get(['id', 'code', 'name']);
        $byCode = $accounts->keyBy(fn($a) => mb_strtolower(trim((string) $a->code)));
        $byName = $accounts->keyBy(fn($a) => mb_strtolower(trim((string) $a->name)));
        $parties = ['customer' => Customer::get(['id', 'name_en', 'name_ar', 'row_no']), 'supplier' => Supplier::get(['id', 'name_en', 'name_ar', 'row_no'])];
        $partyIdx = [];
        foreach ($parties as $kind => $list) {
            foreach ($list as $p) {
                foreach ([$p->name_en, $p->name_ar, $p->row_no] as $key) {
                    if (filled($key)) $partyIdx[$kind][mb_strtolower(trim((string) $key))] = $p;
                }
            }
        }
        $svc = app(\App\Services\Finance\OpeningBalanceService::class);
        $arId = $svc->arId();
        $apId = $svc->apId();
        $haveCust = $existingOb->whereNotNull('customer_id')->pluck('customer_id')->flip();
        $haveSupp = $existingOb->whereNotNull('supplier_id')->pluck('supplier_id')->flip();
        $haveAcct = $existingOb->whereNull('customer_id')->whereNull('supplier_id')->where('is_auto_generated', 0)->pluck('account_id')->flip();

        $cell = fn($row, $f) => isset($map[$f]) ? ($row[$map[$f]] ?? '') : '';
        $seen = [];
        $out = [];
        foreach ($data['rows'] as $i => $row) {
            $name = trim((string) $cell($row, 'name'));
            $code = trim((string) $cell($row, 'code'));
            $r = ['row' => $i, 'name' => $name ?: $code, 'matched' => null, 'kind' => null, 'debit' => 0.0, 'credit' => 0.0, 'notes' => trim((string) $cell($row, 'notes')),
                'account_id' => null, 'customer_id' => null, 'supplier_id' => null, 'status' => 'new', 'note' => ''];

            if ($name === '' && $code === '') { $r['status'] = 'skip'; $r['note'] = __('Empty row'); $out[] = $r; continue; }

            // Amounts
            $dr = isset($map['debit']) ? $this->amount($cell($row, 'debit')) : 0.0;
            $cr = isset($map['credit']) ? $this->amount($cell($row, 'credit')) : 0.0;
            if ($dr === null || $cr === null) { $r['status'] = 'invalid'; $r['note'] = __('Amount is not a number'); $out[] = $r; continue; }
            if (isset($map['amount']) && !$dr && !$cr) {
                $raw = (string) $cell($row, 'amount');
                $amt = $this->amount($raw);
                if ($amt === null) { $r['status'] = 'invalid'; $r['note'] = __('Amount is not a number'); $out[] = $r; continue; }
                $side = mb_strtolower(trim((string) $cell($row, 'drcr') . ' ' . (preg_match('/\b(dr|cr)\b\.?$/i', trim($raw), $m) ? $m[1] : '')));
                $isCr = str_contains($side, 'cr');
                if ($amt < 0) { $isCr = !$isCr; $amt = abs($amt); }
                $dr = $isCr ? 0.0 : $amt;
                $cr = $isCr ? $amt : 0.0;
            }
            if ($dr < 0) { $cr += abs($dr); $dr = 0.0; }
            if ($cr < 0) { $dr += abs($cr); $cr = 0.0; }
            if ($dr > 0 && $cr > 0) { $net = round($dr - $cr, 2); $dr = $net > 0 ? $net : 0.0; $cr = $net < 0 ? abs($net) : 0.0; }
            $r['debit'] = round($dr, 2); $r['credit'] = round($cr, 2);
            if ($r['debit'] == 0 && $r['credit'] == 0) { $r['status'] = 'skip'; $r['note'] = __('No amount'); $out[] = $r; continue; }

            // What is it?
            $kindCell = mb_strtolower(trim((string) $cell($row, 'kind')));
            $kind = match (true) {
                str_contains($kindCell, 'customer') || $kindCell === 'ar' || str_contains($kindCell, 'receiv') => 'customer',
                str_contains($kindCell, 'supplier') || str_contains($kindCell, 'vendor') || $kindCell === 'ap' || str_contains($kindCell, 'payab') => 'supplier',
                $kindCell !== '' && in_array($kindCell, ['account', 'ledger', 'gl'], true) => 'account',
                default => $default,
            };
            $key = mb_strtolower($name);
            $hits = [];
            $tryAccount = function () use ($code, $key, $byCode, $byName) {
                $a = ($code !== '' ? ($byCode[mb_strtolower($code)] ?? null) : null) ?? ($key !== '' ? ($byName[$key] ?? null) : null) ?? ($key !== '' ? ($byCode[$key] ?? null) : null);
                return $a;
            };
            if (in_array($kind, ['auto', 'account'], true) && ($a = $tryAccount())) $hits[] = ['account', $a];
            foreach (['customer', 'supplier'] as $k) {
                if (in_array($kind, ['auto', $k], true)) {
                    $p = ($key !== '' ? ($partyIdx[$k][$key] ?? null) : null) ?? ($code !== '' ? ($partyIdx[$k][mb_strtolower($code)] ?? null) : null);
                    if ($p) $hits[] = [$k, $p];
                }
            }
            if (!$hits) { $r['status'] = 'invalid'; $r['note'] = __('Not found in your accounts, customers or suppliers'); $out[] = $r; continue; }
            if (count($hits) > 1) { $r['status'] = 'invalid'; $r['note'] = __('Matches more than one of account / customer / supplier: add a Type column'); $out[] = $r; continue; }

            [$k, $m] = $hits[0];
            $r['kind'] = $k;
            if ($k === 'account') {
                $r['account_id'] = $m->id; $r['matched'] = trim($m->code . ' · ' . $m->name, ' ·');
                $dupe = "a{$m->id}";
                if (isset($haveAcct[$m->id])) { $r['status'] = 'exists'; $r['note'] = __('Already has an opening balance'); }
                elseif (in_array((int) $m->id, [$arId, $apId], true)) { $r['status'] = 'invalid'; $r['note'] = __('Receivables and payables are entered per customer / supplier'); }
            } elseif ($k === 'customer') {
                $r['customer_id'] = $m->id; $r['account_id'] = $arId; $r['matched'] = $m->name_en;
                $dupe = "c{$m->id}";
                if (isset($haveCust[$m->id])) { $r['status'] = 'exists'; $r['note'] = __('Already has an opening balance'); }
            } else {
                $r['supplier_id'] = $m->id; $r['account_id'] = $apId; $r['matched'] = $m->name_en;
                $dupe = "s{$m->id}";
                if (isset($haveSupp[$m->id])) { $r['status'] = 'exists'; $r['note'] = __('Already has an opening balance'); }
            }
            if ($r['status'] === 'new') {
                if (isset($seen[$dupe])) { $r['status'] = 'invalid'; $r['note'] = __('Listed twice in this file (row :n)', ['n' => $seen[$dupe] + 2]); }
                else $seen[$dupe] = $i;
            }
            $out[] = $r;
        }

        $good = collect($out)->where('status', 'new');
        $debit = round($good->sum('debit'), 2);
        $credit = round($good->sum('credit'), 2);

        return [
            'rows' => $out, 'date' => $date, 'locked' => (bool) $locked,
            'totals' => ['debit' => $debit, 'credit' => $credit, 'diff' => round($debit - $credit, 2)],
            'summary' => [
                'total' => count($out), 'new' => $good->count(),
                'exists' => collect($out)->where('status', 'exists')->count(), 'invalid' => collect($out)->where('status', 'invalid')->count(), 'skip' => collect($out)->where('status', 'skip')->count(),
            ],
        ];
    }
}
