<?php

namespace App\Http\Controllers\Import;

use App\Enums\CustomerStatusEnum;
use App\Exceptions\AiTokenLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Supplier\Supplier;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Customer and supplier import, nothing is saved until the last step:
 *  1. upload():  a spreadsheet is kept server-side under a token; its columns come back with a suggested match
 *                to our fields. A PDF / photo is read by AI and goes straight to step 3.
 *  2. preview(): the customer's column match is applied and every row comes back checked.
 *  3. import():  the reviewed rows are checked again server-side and saved in one transaction.
 * The route passes $kind: customer | supplier.
 */
class PartyImportController extends Controller
{
    private const MAX_ROWS = 5000;

    /** Customers only: matched by name to the active salespersons. */
    private const SALESPERSON_WORDS = ['salesperson', 'sales person', 'salesman', 'sales man', 'sales executive', 'account manager', 'مندوب المبيعات', 'المندوب'];

    /** Field => [max length, header words we recognise]. */
    public const FIELDS = [
        'name_en' => [255, ['name', 'name en', 'name english', 'english name', 'customer name', 'supplier name', 'vendor name', 'company name', 'company', 'party name', 'party', 'account name', 'customer', 'supplier', 'vendor', 'client', 'client name', 'business name', 'trade name', 'الاسم بالانجليزي', 'الاسم بالإنجليزي']],
        'name_ar' => [255, ['name ar', 'name arabic', 'arabic name', 'name in arabic', 'language name', 'local name', 'arabic', 'الاسم', 'الاسم بالعربي', 'اسم العميل', 'اسم المورد', 'الاسم العربي']],
        'email' => [255, ['email', 'e mail', 'email address', 'mail', 'البريد الالكتروني', 'البريد الإلكتروني']],
        'phone' => [16, ['phone', 'mobile', 'phone number', 'mobile number', 'telephone', 'tel', 'contact number', 'contact no', 'phone no', 'mobile no', 'الجوال', 'الهاتف', 'رقم الجوال']],
        'alt_phone' => [16, ['alt phone', 'alternate phone', 'alternative phone', 'phone 2', 'mobile 2', 'other phone', 'landline']],
        'contact_person' => [100, ['contact person', 'contact', 'contact name', 'attention', 'attn']],
        'vat_number' => [50, ['vat', 'vat number', 'vat no', 'tax number', 'tax no', 'trn', 'tax id', 'vat registration number', 'vat reg no', 'الرقم الضريبي']],
        'cr_number' => [50, ['cr', 'cr number', 'cr no', 'commercial registration', 'commercial registration number', 'registration number', 'السجل التجاري']],
        'address1_en' => [255, ['address', 'address 1', 'address line 1', 'address1', 'street', 'street name', 'address en', 'address english']],
        'address1_ar' => [255, ['address ar', 'address arabic', 'arabic address', 'العنوان']],
        'address2_en' => [255, ['address 2', 'address line 2', 'address2', 'district', 'area', 'territory', 'territory area']],
        'city_en' => [100, ['city', 'city en', 'city english', 'town']],
        'city_ar' => [100, ['city ar', 'city arabic', 'المدينة']],
        'building_number' => [10, ['building', 'building no', 'building number']],
        'plot_no' => [10, ['plot', 'plot no', 'additional number', 'additional no', 'secondary number']],
        'postal_code' => [20, ['postal code', 'zip', 'zip code', 'postcode', 'post code', 'po box', 'p o box', 'الرمز البريدي']],
        'country' => [100, ['country', 'الدولة']],
        'currency' => [10, ['currency', 'currency code', 'curr', 'العملة']],
        'credit_limit' => [20, ['credit limit', 'limit']],
        'credit_days' => [10, ['credit days', 'credit period', 'payment days', 'due days', 'credit terms']],
        'iban' => [34, ['iban', 'iban number', 'bank account']],
    ];

    public function page(string $kind)
    {
        return view('modules.common.party-import', ['kind' => $kind]);
    }

    /** A small example file showing the columns we understand. */
    public function sample(string $kind)
    {
        $rows = [
            ['name_en', 'name_ar', 'email', 'phone', 'vat_number', 'cr_number', 'address1_en', 'city_en', 'building_number', 'plot_no', 'postal_code', 'country', 'currency', 'credit_limit', 'credit_days'],
            ['Al Noor Trading Co', 'شركة النور التجارية', 'accounts@alnoor.example', '+966501234567', '300000000000003', '1010101010', 'King Fahd Road', 'Riyadh', '1234', '5678', '12345', 'Saudi Arabia', 'SAR', '50000', '30'],
            ['Gulf Star Logistics', '', 'info@gulfstar.example', '+971501234567', '', '', '', 'Dubai', '', '', '', 'United Arab Emirates', 'AED', '', ''],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $r) {
                fputcsv($out, $r, ',', '"', '');
            }
            fclose($out);
        }, "{$kind}s-sample.csv", ['Content-Type' => 'text/csv']);
    }

    public function upload(Request $request, GeminiService $gemini, string $kind): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt,pdf,jpg,jpeg,png,webp|max:10240']);
        @set_time_limit(300);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        try {
            if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
                // A PDF or photo has no columns to match: AI reads it into our fields.
                $raw = $this->mapDocument($file, $gemini, $kind);
                if ($raw === null) {
                    return response()->json(['error' => __('AI could not analyse the file right now. Please try again in a moment.')], 500);
                }
                if (!$raw) {
                    return response()->json(['error' => __('No rows were found in this file.')], 422);
                }

                return response()->json(['mode' => 'rows'] + $this->checked($this->normalise($raw, $kind)));
            }
            $data = $this->cells($this->load($file->getRealPath(), $ext)->getActiveSheet());
        } catch (AiTokenLimitExceededException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => __('Could not read this file. Please check it and try again.')], 422);
        }

        $data = array_values(array_filter($data, fn ($r) => (bool) array_filter($r, fn ($c) => $c !== '')));
        [$headerAt, $suggest] = $this->findHeader($data, $kind);
        if ($headerAt === null || count($data) - $headerAt - 1 < 1) {
            return response()->json(['error' => __('No rows were found in this file.')], 422);
        }
        $headers = $data[$headerAt];
        $width = count($headers);
        $rows = array_map(fn ($r) => array_pad(array_slice($r, 0, $width), $width, ''), array_slice($data, $headerAt + 1, self::MAX_ROWS));

        // uploads that were never imported are kept for a day
        foreach (Storage::disk('local')->files('party-imports') as $old) {
            if (Storage::disk('local')->lastModified($old) < now()->subDay()->getTimestamp()) Storage::disk('local')->delete($old);
        }
        $token = Str::random(40);
        Storage::disk('local')->put("party-imports/$token.json", json_encode(['company_id' => companyId(), 'kind' => $kind, 'headers' => $headers, 'rows' => $rows]));

        // One example per column (first non-empty value), shown beside the match boxes.
        $examples = [];
        foreach (array_keys($headers) as $i) {
            $examples[$i] = collect($rows)->pluck($i)->first(fn ($v) => $v !== '') ?? '';
        }

        return response()->json(['mode' => 'map', 'token' => $token, 'headers' => $headers, 'examples' => $examples, 'total' => count($rows), 'suggest' => $suggest]);
    }

    public function preview(Request $request, string $kind): JsonResponse
    {
        $stored = $this->stored($request->input('token'), $kind);
        if (!$stored) {
            return response()->json(['error' => __('This upload has expired. Please upload the file again.')], 422);
        }
        $map = $this->mapping($request, count($stored['headers']), $kind);
        if (!isset($map['name_en']) && !isset($map['name_ar'])) {
            return response()->json(['error' => __('Choose which column holds the name (English or Arabic).')], 422);
        }

        $raw = array_map(fn ($row) => array_map(fn ($i) => $row[$i] ?? '', $map), $stored['rows']);

        return response()->json($this->checked($this->normalise($raw, $kind)));
    }

    private function checked(array $rows): array
    {
        return [
            'rows' => $rows,
            'summary' => [
                'total' => count($rows),
                'new' => collect($rows)->where('status', 'new')->count(),
                'skipped' => collect($rows)->where('status', 'exists')->count(),
                'invalid' => collect($rows)->where('status', 'invalid')->count(),
            ],
        ];
    }

    private function stored(?string $token, string $kind): ?array
    {
        if (!$token || !preg_match('/^[A-Za-z0-9]{40}$/', $token) || !Storage::disk('local')->exists("party-imports/$token.json")) {
            return null;
        }
        $d = json_decode(Storage::disk('local')->get("party-imports/$token.json"), true);

        // a token only works for the company (and list) that uploaded it
        return ($d && (int) $d['company_id'] === (int) companyId() && $d['kind'] === $kind) ? $d : null;
    }

    /** field => column index, from the request; only our fields, only columns that exist. */
    private function mapping(Request $request, int $width, string $kind): array
    {
        $allowed = $this->fieldNames($kind);
        $map = [];
        foreach ((array) $request->input('map', []) as $field => $col) {
            if (in_array($field, $allowed, true) && is_numeric($col) && (int) $col >= 0 && (int) $col < $width) {
                $map[$field] = (int) $col;
            }
        }

        return $map;
    }

    private function fieldNames(string $kind): array
    {
        return [...array_keys(self::FIELDS), ...($kind === 'customer' ? ['salesperson'] : [])];
    }

    public function import(Request $request, string $kind): JsonResponse
    {
        $rules = ['rows' => 'required|array|min:1|max:5000'];
        foreach ($this->fieldNames($kind) as $field) {
            $rules["rows.*.$field"] = 'nullable|string|max:1000';
        }
        $data = $request->validate($rules);

        // Re-run the same checks on what the customer approved: never trust the browser.
        $rows = collect($this->normalise($data['rows'], $kind))->where('status', 'new')->values();
        if ($rows->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => __('Nothing to import.')], 422);
        }

        $model = $kind === 'customer' ? Customer::class : Supplier::class;
        DB::beginTransaction();
        try {
            // Same numbering as the create form: CS / SP + running number.
            $next = (int) ($model::lockForUpdate()->max('unique_row_no') ?? ($kind === 'customer' ? 0 : 26000));
            foreach ($rows as $r) {
                $party = new $model();
                $this->setBaseColumns($party);
                $party->unique_row_no = sprintf('%03d', ++$next);
                $party->row_no = ($kind === 'customer' ? 'CS' : 'SP') . $party->unique_row_no;
                $party->status = $kind === 'customer' ? CustomerStatusEnum::CONFIRMED->value : 1;
                foreach (array_keys(self::FIELDS) as $field) {
                    $party->{$field} = $r[$field] === '' ? null : $r[$field];
                }
                $party->business_type = $r['vat_number'] !== '' ? 'registered' : 'unregistered';
                if ($kind === 'customer') {
                    $party->salesperson_id = $r['salesperson_id'];
                }
                $party->save();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['status' => 'error', 'message' => __('Import failed, nothing was saved: ') . $e->getMessage()], 500);
        }
        if ($kind === 'customer') {
            Cache::forget('confirmCustomers:' . cacheName());
        }
        if ($this->stored($request->input('token'), $kind)) {
            Storage::disk('local')->delete('party-imports/' . $request->input('token') . '.json');
        }

        $message = $kind === 'customer' ? __(':n customers imported.', ['n' => $rows->count()]) : __(':n suppliers imported.', ['n' => $rows->count()]);

        return response()->json(['status' => 'success', 'message' => $message, 'created' => $rows->count()]);
    }

    // ---------------------------------------------------------------- reading the file

    /**
     * Phone, VAT and CR numbers must come through exactly as typed: a CSV is read as text (otherwise "+966…" loses
     * its "+"), and a number stored as a number in Excel is written out in full (not 3.0E+14).
     */
    private function load(string $path, string $ext): Spreadsheet
    {
        if (!in_array($ext, ['csv', 'txt'], true)) {
            return IOFactory::load($path);
        }
        $binder = Cell::getValueBinder();
        Cell::setValueBinder(new StringValueBinder());
        try {
            return IOFactory::load($path);
        } finally {
            Cell::setValueBinder($binder);
        }
    }

    /** Every sheet row as trimmed strings. */
    private function cells(Worksheet $sheet): array
    {
        return array_map(fn ($row) => array_map(function ($c) {
            if (is_float($c) || is_int($c)) {
                return floor($c) == $c && abs($c) < 1e15 ? sprintf('%.0f', $c) : (string) $c;
            }

            return is_scalar($c) ? trim(strip_tags((string) $c)) : '';
        }, $row), $sheet->toArray(null, true, false, false));
    }

    /** Header words compare without case, punctuation or extra spaces ("E-mail" = "email", "Name (Arabic)" = "name arabic"). */
    private function headerKey(string $h): string
    {
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($h)));
    }

    /**
     * The header row (the first of the top 15 rows naming a name column, else the first row with two filled cells)
     * and our suggested match: field => column index.
     */
    private function findHeader(array $data, string $kind): array
    {
        $words = array_map(fn ($f) => $f[1], self::FIELDS);
        if ($kind === 'customer') $words['salesperson'] = self::SALESPERSON_WORDS;

        $fallback = null;
        foreach (array_slice($data, 0, 15, true) as $at => $cells) {
            $heads = array_map(fn ($c) => $this->headerKey($c), $cells);
            $cols = [];
            foreach ($words as $field => $list) {
                foreach (array_map(fn ($w) => $this->headerKey($w), [$field, ...$list]) as $w) {
                    $i = array_search($w, $heads, true);
                    if ($i !== false && !in_array($i, $cols, true)) {
                        $cols[$field] = $i;
                        break;
                    }
                }
            }
            if (isset($cols['name_en']) || isset($cols['name_ar'])) {
                return [$at, $cols];
            }
            if ($fallback === null && count(array_filter($cells, fn ($c) => $c !== '')) >= 2) {
                $fallback = [$at, $cols];
            }
        }

        return $fallback ?? [null, []];
    }

    private function prompt(string $kind, string $source): string
    {
        $fields = implode(', ', array_map(fn ($f) => "\"$f\"", array_keys(self::FIELDS)));

        return <<<PROMPT
You are reading a list of {$kind}s (any layout, any language) to import into our system.
For every {$kind} return an object with these keys: {$fields}.
- "name_en": the name in English (or Latin letters); "name_ar": the name in Arabic if shown. Empty string when not shown.
- "phone" / "alt_phone": as printed. "vat_number": VAT / tax number. "cr_number": commercial registration number.
- "country": country name. "currency": 3-letter currency code if shown. "credit_limit": number only. "credit_days": whole days only.
- Any value not in the source is an empty string. Never invent values.
SKIP: titles, blank rows, column headers, "Total ..." rows.
Return ONLY JSON: {"rows":[{...}]} in the same order as the source.

SOURCE ({$source}):
PROMPT;
    }

    /** PDF / image: the model reads the document directly. */
    private function mapDocument($file, GeminiService $gemini, string $kind): ?array
    {
        $item = ['type' => 'OBJECT', 'properties' => collect(array_keys(self::FIELDS))->mapWithKeys(fn ($k) => [$k => ['type' => 'STRING']])->all(), 'required' => ['name_en', 'name_ar']];
        $schema = ['type' => 'OBJECT', 'properties' => ['rows' => ['type' => 'ARRAY', 'items' => $item]], 'required' => ['rows']];

        $response = $gemini->generateVisionResponse($this->prompt($kind, 'the attached document'), base64_encode(file_get_contents($file->getRealPath())), $file->getMimeType(), true, $schema, 'Import ' . ucfirst($kind) . 's');
        $decoded = $response ? json_decode($this->sanitize($response), true) : null;

        return is_array($decoded) ? array_values(array_filter($decoded['rows'] ?? [], 'is_array')) : null;
    }

    private function sanitize(string $r): string
    {
        $r = trim(preg_replace(['/^```(?:json)?\s*/i', '/\s*```$/'], '', trim($r)));
        $s = strpos($r, '{');
        $e = strrpos($r, '}');

        return ($s !== false && $e !== false) ? substr($r, $s, $e - $s + 1) : $r;
    }

    // ---------------------------------------------------------------- checking the rows

    private function nameKey(?string $s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $s)));
    }

    private function number(string $v): ?float
    {
        $v = str_replace([',', ' '], '', $v);

        return is_numeric($v) ? (float) $v : null;
    }

    /** "SA", "KSA", "saudi arabia" => "Saudi Arabia" (the create form stores the country name). */
    private function country(string $v, array $byKey): ?string
    {
        $aliases = ['ksa' => 'sa', 'saudi' => 'sa', 'uae' => 'ae', 'emirates' => 'ae', 'uk' => 'gb', 'usa' => 'us'];
        $k = mb_strtolower(trim($v));
        $k = $aliases[$k] ?? $k;
        // "GREAT BRITAIN (UNITED KINGDOM)": try the part in brackets too
        if (!isset($byKey[$k]) && preg_match('/\(([^)]+)\)/', $k, $m)) {
            $k = $aliases[trim($m[1])] ?? trim($m[1]);
        }

        return $byKey[$k] ?? ['turkey' => $byKey['tr'] ?? null][$k] ?? null;
    }

    /**
     * Turns raw rows into reviewed rows: cleaned values, defaults for country / currency, and a status:
     * new | exists (already a customer / supplier, skipped) | invalid (never imported). Adjustments are listed in "note".
     */
    private function normalise(array $raw, string $kind): array
    {
        $model = $kind === 'customer' ? Customer::class : Supplier::class;
        $existing = $model::get(['id', 'row_no', 'name_en', 'name_ar', 'vat_number']);
        $byName = [];
        $byVat = [];
        foreach ($existing as $p) {
            $label = $p->row_no ?: $p->name_en;
            foreach ([$p->name_en, $p->name_ar] as $n) {
                if (filled($n)) $byName[$this->nameKey($n)] = $label;
            }
            if (filled($p->vat_number)) $byVat[preg_replace('/\s+/', '', $p->vat_number)] = $label;
        }

        $countries = [];
        foreach (countries() as $code => $name) {
            $countries[mb_strtolower($code)] = $name;
            $countries[mb_strtolower($name)] = $name;
        }
        $currencies = DB::table('currencies')->pluck('code')->map(fn ($c) => strtoupper($c))->flip();
        $company = DB::table('companies')->where('id', companyId())->first(['country', 'currency']);
        $defaultCountry = $this->country((string) ($company->country ?? ''), $countries) ?? 'Saudi Arabia';
        $defaultCurrency = strtoupper((string) ($company->currency ?? '')) ?: 'SAR';
        $salespersons = $kind === 'customer' ? \App\Models\Master\Salesperson\Salesperson::activeSalesperson()->keyBy(fn ($sp) => $this->nameKey($sp->name)) : collect();

        $seenName = [];
        $seenVat = [];
        $rows = [];
        foreach (array_values($raw) as $i => $in) {
            $notes = [];
            $r = [];
            foreach ([...array_keys(self::FIELDS), 'salesperson'] as $field) {
                // a leading ' is Excel's "keep as text" mark, not part of the value
                $v = ltrim(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($in[$field] ?? '')))), "'");
                // exports fill empty cells with placeholders: "N/A", "-", " -  / ", "UNREGISTERED"
                $r[$field] = preg_match('/^(n\/?a|nil|none|null|-|0|unregistered|not registered)$/i', $v) || !preg_match('/[\p{L}\p{N}]/u', $v) ? '' : $v;
            }
            $status = 'new';

            // An export that lost its Arabic letters prints "¿¿¿¿ ¿¿¿" or "???": unusable, treat as empty.
            $arRaw = str_replace(' ', '', (string) ($in['name_ar'] ?? ''));
            if ($arRaw !== '' && preg_match_all('/[?¿\x{FFFD}]/u', $arRaw) >= mb_strlen($arRaw) / 2) {
                $r['name_ar'] = '';
                $notes[] = __('Arabic name in the file is unreadable (the export lost the Arabic letters)');
            }

            // Names: the create form needs both, so a missing one is copied from the other.
            if ($r['name_en'] === '' && $r['name_ar'] === '') {
                $status = 'invalid';
                $notes[] = __('No name');
            } elseif ($r['name_ar'] === '') {
                $r['name_ar'] = $r['name_en'];
                $notes[] = __('Arabic name copied from the English name');
            } elseif ($r['name_en'] === '') {
                $r['name_en'] = $r['name_ar'];
                $notes[] = __('English name copied from the Arabic name');
            }

            if ($r['email'] !== '') {
                // "<a@b.com>", "E-mail:a@b.com", "a@b.com , c@d.com": keep the first address
                $emails = preg_match_all('/[^\s<>,;:"]+@[^\s<>,;:"]+\.[a-z]{2,}/i', $r['email'], $m) ? $m[0] : [];
                if (count($emails) > 1) $notes[] = __('More than one email, kept the first: :v', ['v' => $emails[0]]);
                $r['email'] = mb_strtolower($emails[0] ?? $r['email']);
                if (!filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
                    $notes[] = __('Email ":v" is not valid, left empty', ['v' => $r['email']]);
                    $r['email'] = '';
                }
            }
            if ($r['country'] === '') {
                $r['country'] = $defaultCountry;
            } elseif ($c = $this->country($r['country'], $countries)) {
                $r['country'] = $c;
            } else {
                $notes[] = __('Country ":v" not recognised, set to :d', ['v' => $r['country'], 'd' => $defaultCountry]);
                $r['country'] = $defaultCountry;
            }

            // Extensions and remarks are not part of the number: "Ext:128", "[302]", "# 363", "(Office No.)".
            foreach (['phone', 'alt_phone'] as $f) {
                $r[$f] = trim(preg_replace(['/\s*(ext\.?|extn\.?|x|#)\s*:?\s*\d+\s*$/i', '/\[[^\]]*\]/', '/\((?![\s+\d]+\))[^)]*\)/', '/^[^\d+(]+/', '/[.\s]+$/'], '', $r[$f]));
            }
            // Two numbers in one phone cell ("0564730621 / 0574757350"): the second becomes the alternate phone,
            // a short second part ("+966 13 8579922 / 1278") is an extension and is dropped.
            $phones = preg_split('/\s*[\/,;|]\s*/', $r['phone'], -1, PREG_SPLIT_NO_EMPTY);
            if (count($phones) > 1) {
                $r['phone'] = $phones[0];
                if ($r['alt_phone'] === '' && strlen(preg_replace('/\D/', '', $phones[1])) >= 7) $r['alt_phone'] = $phones[1];
            }
            foreach (['phone', 'alt_phone'] as $f) {
                if ($r[$f] === '') continue;
                $p = preg_replace('/[\s\-().]/', '', $r[$f]);
                $p = preg_replace('/^\+\s*|^00/', '+', $p);
                if (preg_match('/^\+?\d{1,4}$/', $p)) {                       // only a country code ("00966"): no number
                    $r[$f] = '';
                    continue;
                }
                if ($r['country'] === 'Saudi Arabia' && preg_match('/^5\d{8}$/', $p)) {
                    $p = '0' . $p;                                            // mobile typed without its leading 0
                } elseif (preg_match('/^[1-9]\d{10,14}$/', $p)) {
                    $p = '+' . $p;                                            // international number typed without "+"
                }
                if (!preg_match('/^\+?[0-9]{10,15}$/', $p)) {
                    $notes[] = __('Phone ":v" is not 10 to 15 digits, left empty', ['v' => $r[$f]]);
                    $p = '';
                }
                $r[$f] = $p;
            }

            // VAT and CR often share one column ("312041894900003/ 1010287602"): the 15-digit number is the VAT,
            // the other one the CR. Works whether the column is matched to VAT, to CR, or to both.
            // Labels typed into the cell ("CR:", "CRNO:", "VAT No.") are dropped. Only two real numbers (7+ digits each)
            // are split: "13.579.687/0001-03" is one foreign tax id.
            $label = '/^(vat|trn|tax|c\.?\s*r\.?)\s*(no\.?|number|#)?\s*[:\-#.]?\s*/i';
            $parts = collect([$r['vat_number'], $r['cr_number']])
                ->flatMap(fn ($v) => preg_split('/\s*[\/,;|]\s*|\s{2,}/', $v, -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn ($v) => preg_replace('/\s+/', '', preg_replace($label, '', ltrim($v, "'"))))->filter()->unique()->values();
            $split = $parts->count() > 1 && $parts->every(fn ($v) => strlen(preg_replace('/\D/', '', $v)) >= 7);
            if ($split) {
                $vat = $parts->first(fn ($v) => preg_match('/^3\d{13}3$/', $v)) ?? $parts->first(fn ($v) => preg_match('/^\d{15}$/', $v)) ?? $parts[0];
                $cr = $parts->first(fn ($v) => $v !== $vat);
                $notes[] = __('VAT and CR were in one column: split into VAT :v and CR :c', ['v' => $vat, 'c' => $cr]);
                $r['vat_number'] = $vat;
                $r['cr_number'] = $cr;
            } else {
                $r['vat_number'] = preg_replace('/\s+/', '', preg_replace($label, '', $r['vat_number']));
                $r['cr_number'] = preg_replace('/\s+/', '', preg_replace($label, '', $r['cr_number']));
                // a Saudi CR (10 digits) alone in the VAT column
                if ($r['country'] === 'Saudi Arabia' && $r['cr_number'] === '' && preg_match('/^[1-9]\d{9}$/', $r['vat_number'])) {
                    $notes[] = __(':v is a CR number, not a VAT number: moved to CR', ['v' => $r['vat_number']]);
                    $r['cr_number'] = $r['vat_number'];
                    $r['vat_number'] = '';
                }
            }
            $r['iban'] = strtoupper(preg_replace('/\s+/', '', $r['iban']));

            if ($r['vat_number'] !== '' && $r['country'] === 'Saudi Arabia' && !preg_match('/^3\d{13}3$/', $r['vat_number'])) {
                $notes[] = __('Saudi VAT numbers are 15 digits starting and ending with 3: please check');
            }

            $r['salesperson_id'] = null;
            if ($kind === 'customer' && $r['salesperson'] !== '') {
                $sp = $salespersons[$this->nameKey($r['salesperson'])] ?? null;
                if ($sp) {
                    $r['salesperson_id'] = $sp->id;
                    $r['salesperson'] = $sp->name;
                } else {
                    $notes[] = __('Salesperson ":v" not found, left empty', ['v' => $r['salesperson']]);
                    $r['salesperson'] = '';
                }
            }

            $cur = strtoupper($r['currency']);
            $cur = ['SR' => 'SAR', 'RIYAL' => 'SAR', 'DHS' => 'AED', 'DIRHAM' => 'AED', '$' => 'USD'][$cur] ?? $cur;
            if ($cur === '') {
                $cur = $defaultCurrency;
            } elseif (!isset($currencies[$cur])) {
                $notes[] = __('Currency ":v" not recognised, set to :d', ['v' => $r['currency'], 'd' => $defaultCurrency]);
                $cur = $defaultCurrency;
            }
            $r['currency'] = $cur;

            if ($r['credit_limit'] !== '') {
                $n = $this->number($r['credit_limit']);
                if ($n === null || $n < 0) {
                    $notes[] = __('Credit limit ":v" is not a number, left empty', ['v' => $r['credit_limit']]);
                    $r['credit_limit'] = '';
                } else {
                    $r['credit_limit'] = (string) round($n, 2);
                }
            }
            if ($r['credit_days'] !== '') {
                $n = $this->number(preg_replace('/\s*days?$/i', '', $r['credit_days']));
                if ($n === null || $n < 0) {
                    $notes[] = __('Credit days ":v" is not a number, left empty', ['v' => $r['credit_days']]);
                    $r['credit_days'] = '';
                } else {
                    $r['credit_days'] = (string) (int) $n;
                }
            }

            foreach (self::FIELDS as $field => [$max]) {
                if (mb_strlen($r[$field]) > $max) {
                    $status = 'invalid';
                    $notes[] = __(':f is longer than :n characters', ['f' => $field, 'n' => $max]);
                }
            }

            // Already in the system, or twice in the file.
            if ($status === 'new') {
                $vat = $r['vat_number'];
                $hit = $byName[$this->nameKey($r['name_en'])] ?? $byName[$this->nameKey($r['name_ar'])] ?? null;
                if ($hit) {
                    $status = 'exists';
                    $notes = [__('Already exists as :no', ['no' => $hit])];
                } else {
                    // Group companies often share one VAT number: a warning, not a block.
                    if ($vat !== '' && ($other = $byVat[$vat] ?? (isset($seenVat[$vat]) ? __('row :n', ['n' => $seenVat[$vat] + 1]) : null))) {
                        $notes[] = __('Same VAT number as :no: please check', ['no' => $other]);
                    }
                    $twin = $seenName[$this->nameKey($r['name_en'])] ?? null;
                    if ($twin !== null) {
                        $status = 'invalid';
                        $notes[] = __('Listed twice in this file (row :n)', ['n' => $twin + 1]);
                    } else {
                        $seenName[$this->nameKey($r['name_en'])] = $i;
                        if ($vat !== '' && !isset($seenVat[$vat])) $seenVat[$vat] = $i;
                    }
                }
            }

            $rows[] = $r + ['status' => $status, 'note' => implode('; ', array_unique($notes))];
        }

        return $rows;
    }
}
