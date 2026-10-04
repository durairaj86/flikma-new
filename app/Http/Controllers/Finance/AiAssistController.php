<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Expense\Expense;
use App\Models\Master\Description;
use App\Models\Master\Unit;
use App\Models\Supplier\Supplier;
use App\Services\GeminiService;
use App\Services\SequenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistController extends Controller
{
    public function scanReceipt(Request $request, GeminiService $gemini): JsonResponse
    {
        $request->validate(['image' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240']);
        $file = $request->file('image');
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        $prompt = <<<PROMPT
You are extracting data from an expense receipt / purchase bill for an accounting system.
Return ONLY a JSON object with these exact keys:
- "vendor_name": merchant / supplier name as printed. Empty string if not visible.
- "vendor_email", "vendor_phone", "vendor_address", "vendor_city", "vendor_tax_number": as printed, else empty string.
- "reference_number": receipt / invoice number printed on the document, else empty string.
- "expense_date": YYYY-MM-DD. Use today's date if not visible.
- "subtotal": total BEFORE tax (number).
- "tax_amount": total tax (number, 0 if none).
- "tax_rate": VAT percentage (15 for 15%). Use 0 if none is shown - never guess.
- "currency": 3-letter code if identifiable, else empty string.
- "category": short expense category (fuel, meals, office supplies, ...).
- "description": one short line describing what was bought.
- "line_items": array with one object per purchased line on the receipt (a single object if there is only one), each with:
    - "description": item / service as printed
    - "amount": line amount BEFORE tax (number)
    - "tax_rate": VAT percentage for that line (0 if none shown - never guess)
Today's date is {$this->todayForPrompt()}. Return valid JSON only.
PROMPT;

        $schema = [
            'type' => 'OBJECT',
            'properties' => collect(['vendor_name', 'vendor_email', 'vendor_phone', 'vendor_address', 'vendor_city', 'vendor_tax_number', 'reference_number', 'expense_date', 'currency', 'category', 'description'])
                ->mapWithKeys(fn ($k) => [$k => ['type' => 'STRING']])
                ->merge(['subtotal' => ['type' => 'NUMBER'], 'tax_amount' => ['type' => 'NUMBER'], 'tax_rate' => ['type' => 'NUMBER']])
                ->all(),
            'required' => ['vendor_name', 'expense_date', 'subtotal', 'tax_amount', 'tax_rate', 'description', 'line_items'],
        ];
        $schema['properties']['line_items'] = [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'description' => ['type' => 'STRING'],
                    'amount' => ['type' => 'NUMBER'],
                    'tax_rate' => ['type' => 'NUMBER'],
                ],
                'required' => ['description', 'amount', 'tax_rate'],
            ],
        ];

        $response = $gemini->generateVisionResponse($prompt, $base64, $file->getMimeType(), true, $schema, 'Scan Expense Receipt');
        if (!$response) {
            return response()->json(['error' => 'AI extraction failed. Please fill the form manually.'], 500);
        }

        $decoded = json_decode($this->sanitizeJsonResponse($response), true);
        if (!is_array($decoded)) {
            return response()->json(['error' => 'AI extraction failed. Please fill the form manually.'], 500);
        }

        $vendorName = trim((string) ($decoded['vendor_name'] ?? ''));
        $matchedSupplier = $vendorName ? $this->matchSupplier($vendorName) : null;

        $searchText = trim($vendorName . ' ' . ($decoded['category'] ?? '') . ' ' . ($decoded['description'] ?? ''));
        [$matchedAccount] = $this->resolveExpenseAccount($searchText, $gemini);

        $subtotal = (float) ($decoded['subtotal'] ?? 0);
        $taxAmount = (float) ($decoded['tax_amount'] ?? 0);
        $taxRate = (float) ($decoded['tax_rate'] ?? 0);
        if ($taxRate <= 0 && $subtotal > 0 && $taxAmount > 0) {
            $taxRate = round($taxAmount / $subtotal * 100, 2);
        }

        // One row per bill line; fall back to a single row from the totals.
        $lines = collect($decoded['line_items'] ?? [])
            ->filter(fn ($l) => is_array($l) && (float) ($l['amount'] ?? 0) > 0)
            ->map(fn ($l) => [
                'description' => trim((string) ($l['description'] ?? '')),
                'amount' => (float) $l['amount'],
                'tax_rate' => (float) ($l['tax_rate'] ?? 0),
                'account_id' => null,
                'account_name' => null,
            ])->values()->all();
        if (!$lines) {
            $lines = [[
                'description' => $decoded['description'] ?? '',
                'amount' => $subtotal,
                'tax_rate' => $taxRate,
                'account_id' => $matchedAccount?->id,
                'account_name' => $matchedAccount ? "{$matchedAccount->code} - {$matchedAccount->name}" : null,
            ]];
        } elseif (count($lines) === 1) {
            $lines[0]['account_id'] = $matchedAccount?->id;
            $lines[0]['account_name'] = $matchedAccount ? "{$matchedAccount->code} - {$matchedAccount->name}" : null;
        } else {
            $lines = $this->resolveLineAccounts($lines, $vendorName, $matchedAccount, $gemini);
        }

        return response()->json([
            'line_items' => $lines,
            'vendor_name' => $vendorName,
            'vendor_email' => trim((string) ($decoded['vendor_email'] ?? '')),
            'vendor_phone' => trim((string) ($decoded['vendor_phone'] ?? '')),
            'vendor_address' => trim((string) ($decoded['vendor_address'] ?? '')),
            'vendor_city' => trim((string) ($decoded['vendor_city'] ?? '')),
            'vendor_tax_number' => trim((string) ($decoded['vendor_tax_number'] ?? '')),
            'supplier_id' => $matchedSupplier ? encodeId($matchedSupplier->id) : null,
            'supplier_name' => $matchedSupplier?->name_en,
            'reference_number' => trim((string) ($decoded['reference_number'] ?? '')),
            'expense_date' => $decoded['expense_date'] ?? now()->format('Y-m-d'),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'currency' => strtoupper(trim((string) ($decoded['currency'] ?? ''))) ?: null,
            'account_id' => $matchedAccount?->id,
            'account_name' => $matchedAccount ? "{$matchedAccount->code} - {$matchedAccount->name}" : null,
            'description' => $decoded['description'] ?? '',
        ]);
    }

    public function saveScannedSupplier(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:120',
            'tax_number' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
        ]);

        $existing = Supplier::where('company_id', companyId())
            ->where(function ($q) use ($data) {
                $q->whereRaw('LOWER(name_en) = ?', [strtolower($data['name'])]);
                if (!empty($data['email'])) {
                    $q->orWhereRaw('LOWER(email) = ?', [strtolower($data['email'])]);
                }
            })
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'success',
                'supplier_id' => encodeId($existing->id),
                'party' => ['id' => encodeId($existing->id), 'name' => $existing->name_en],
            ]);
        }

        $supplier = new Supplier();
        $supplier->unique_row_no = sprintf('%03d', (Supplier::max('unique_row_no') ?? 26000) + 1);
        $supplier->row_no = 'SP' . $supplier->unique_row_no;
        $supplier->user_id = auth()->id();
        $supplier->company_id = companyId();
        $supplier->name_en = $data['name'];
        $supplier->name_ar = $data['name'];
        $supplier->currency = $data['currency'] ?? (auth()->user()->company->currency ?? 'SAR');
        $supplier->business_type = !empty($data['tax_number']) ? 'registered' : 'unregistered';
        $supplier->vat_number = $data['tax_number'] ?? null;
        $supplier->country = $data['country'] ?? (auth()->user()->company->country ?? 'SA');
        $supplier->email = $data['email'] ?? null;
        $supplier->phone = $data['phone'] ?? null;
        $supplier->address1_en = $data['address'] ?? null;
        $supplier->city_en = $data['city'] ?? null;
        $supplier->save();

        return response()->json([
            'status' => 'success',
            'supplier_id' => encodeId($supplier->id),
            'party' => ['id' => encodeId($supplier->id), 'name' => $supplier->name_en],
        ]);
    }

    /**
     * Create a Description master record from an unmatched scanned bill line (or
     * return the existing one when the name is already there).
     */
    public function saveScannedDescription(Request $request): JsonResponse
    {
        $data = $request->validate(['description' => 'required|string|max:128']);
        $name = trim($data['description']);

        $existing = Description::where(function ($q) {
            $q->where('company_id', companyId())->orWhereNull('company_id');
        })->whereRaw('LOWER(description) = ?', [strtolower($name)])->first();

        if (!$existing) {
            $existing = new Description();
            $existing->user_id = auth()->id();
            $existing->company_id = companyId();
            $existing->description = $name;
            $existing->description_local = $name;
            $existing->save();
        }

        return response()->json([
            'status' => 'success',
            'description' => ['id' => $existing->id, 'name' => $existing->description],
        ]);
    }

    /**
     * Scan a supplier bill (photo or PDF) and return the supplier, header fields
     * and line items so the supplier-invoice form can be pre-filled.
     */
    public function scanSupplierBill(Request $request, GeminiService $gemini): JsonResponse
    {
        $request->validate(['image' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240']);

        $file = $request->file('image');
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        $prompt = <<<PROMPT
You are extracting structured data from a supplier invoice / purchase bill image for an accounting system.
Return ONLY a JSON object (no markdown, no explanation) with these exact keys:

- "supplier_name": the supplier/vendor company name as printed. Empty string if not visible.
- "supplier_email": the supplier's email as printed. Empty string if not visible.
- "supplier_phone": the supplier's phone as printed. Empty string if not visible.
- "supplier_address": the supplier's street address as printed. Empty string if not visible.
- "supplier_city": the city in the supplier's address. Empty string if not visible.
- "supplier_tax_number": the supplier's VAT / tax registration number. Empty string if not visible.
- "invoice_number": the bill / invoice number printed on the document. Empty string if not visible.
- "invoice_date": YYYY-MM-DD. Use today's date if not visible.
- "due_date": YYYY-MM-DD, or empty string if not shown.
- "currency": 3-letter currency code if identifiable (e.g. "SAR"), else empty string.
- "line_items": array, one object per line on the bill, each with:
    - "description": item / service name as printed
    - "quantity": number
    - "unit": unit of measure as printed (pcs, kg, box ...). Empty string if not shown - do NOT guess.
    - "unit_price": price per unit BEFORE tax
    - "tax_rate": VAT percentage for the line (15 for 15%). Use 0 if none is shown - never guess a non-zero rate.

Today's date is {$this->todayForPrompt()}. Return valid JSON only.
PROMPT;

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'supplier_name' => ['type' => 'STRING'],
                'supplier_email' => ['type' => 'STRING'],
                'supplier_phone' => ['type' => 'STRING'],
                'supplier_address' => ['type' => 'STRING'],
                'supplier_city' => ['type' => 'STRING'],
                'supplier_tax_number' => ['type' => 'STRING'],
                'invoice_number' => ['type' => 'STRING'],
                'invoice_date' => ['type' => 'STRING'],
                'due_date' => ['type' => 'STRING'],
                'currency' => ['type' => 'STRING'],
                'line_items' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'description' => ['type' => 'STRING'],
                            'quantity' => ['type' => 'NUMBER'],
                            'unit' => ['type' => 'STRING'],
                            'unit_price' => ['type' => 'NUMBER'],
                            'tax_rate' => ['type' => 'NUMBER'],
                        ],
                        'required' => ['description', 'quantity', 'unit', 'unit_price', 'tax_rate'],
                    ],
                ],
            ],
            'required' => ['supplier_name', 'invoice_date', 'due_date', 'currency', 'line_items'],
        ];

        $response = $gemini->generateVisionResponse($prompt, $base64, $file->getMimeType(), true, $schema, 'Scan Supplier Bill');
        if (!$response) {
            return response()->json(['error' => 'AI extraction failed. Please fill the form manually.'], 500);
        }

        $decoded = json_decode($this->sanitizeJsonResponse($response), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return response()->json(['error' => 'Could not read the bill clearly. Please fill the form manually.'], 422);
        }

        $supplierName = trim((string) ($decoded['supplier_name'] ?? ''));
        $matchedSupplier = $supplierName ? $this->matchSupplier($supplierName) : null;

        $lineItems = collect($decoded['line_items'] ?? [])->map(function ($line) {
            $description = trim((string) ($line['description'] ?? ''));
            $unitText = trim((string) ($line['unit'] ?? ''));
            $matchedDescription = $description ? $this->matchDescription($description) : null;
            $matchedUnit = $unitText ? $this->matchUnit($unitText) : null;

            return [
                'description' => $description,
                'quantity' => (float) ($line['quantity'] ?? 1),
                'unit_price' => (float) ($line['unit_price'] ?? 0),
                'tax_rate' => (float) ($line['tax_rate'] ?? 0),
                'unit_text' => $unitText ?: null,
                'matched_unit_id' => $matchedUnit?->id,
                'matched_unit_name' => $matchedUnit?->unit_name,
                'matched_description_id' => $matchedDescription?->id,
                'matched_description_name' => $matchedDescription?->description,
            ];
        })->values();

        return response()->json([
            'supplier_name' => $supplierName,
            'supplier_id' => $matchedSupplier ? encodeId($matchedSupplier->id) : null,
            'supplier_email' => trim((string) ($decoded['supplier_email'] ?? '')),
            'supplier_phone' => trim((string) ($decoded['supplier_phone'] ?? '')),
            'supplier_address' => trim((string) ($decoded['supplier_address'] ?? '')),
            'supplier_city' => trim((string) ($decoded['supplier_city'] ?? '')),
            'supplier_tax_number' => trim((string) ($decoded['supplier_tax_number'] ?? '')),
            'invoice_number' => trim((string) ($decoded['invoice_number'] ?? '')),
            'invoice_date' => $decoded['invoice_date'] ?? now()->format('Y-m-d'),
            'due_date' => ($decoded['due_date'] ?? '') ?: null,
            'currency' => strtoupper(trim((string) ($decoded['currency'] ?? ''))) ?: null,
            'line_items' => $lineItems,
        ]);
    }

    private function matchDescription(string $text): ?object
    {
        $needle = strtolower($text);
        $all = Description::descriptions();

        return $all->first(fn ($d) => strtolower($d->description) === $needle)
            ?? $all->first(fn ($d) => $d->description !== '' && (str_contains($needle, strtolower($d->description)) || str_contains(strtolower($d->description), $needle)));
    }

    private function matchUnit(string $text): ?object
    {
        $synonyms = [
            'pcs' => ['pcs', 'pc', 'piece', 'pieces', 'nos', 'no', 'no.', 'each', 'ea', 'unit', 'units'],
            'kg' => ['kg', 'kgs', 'kilogram', 'kilograms'],
            'box' => ['box', 'boxes', 'bx'],
            'ctn' => ['ctn', 'carton', 'cartons'],
        ];
        $needle = strtolower(trim($text));
        foreach ($synonyms as $list) {
            if (in_array($needle, $list, true)) {
                $candidates = $list;
                break;
            }
        }
        $candidates = $candidates ?? [$needle];

        return Unit::units()->first(function ($u) use ($candidates) {
            return in_array(strtolower((string) $u->unit_name), $candidates, true)
                || in_array(strtolower((string) $u->unit_symbol), $candidates, true);
        });
    }

    /** One Gemini call picks an account per line; lines it can't place fall back to the receipt-level account. */
    private function resolveLineAccounts(array $lines, string $vendor, ?object $fallback, GeminiService $gemini): array
    {
        $accounts = Account::active()->where('is_posting', 1)->get(['id', 'code', 'name']);
        $map = [];
        if ($accounts->isNotEmpty()) {
            $accountList = $accounts->map(fn ($a) => "{$a->id}: {$a->code} - {$a->name}")->implode("\n");
            $lineList = collect($lines)->map(fn ($l, $i) => "{$i}: {$l['description']}")->implode("\n");
            $prompt = "Accounts:\n{$accountList}\n\nFor each expense line (vendor: \"{$vendor}\") pick the best account id.\nLines:\n{$lineList}\nReturn ONLY JSON: {\"lines\": [{\"index\": <n>, \"account_id\": <id>}]}";
            $response = $gemini->generateResponse($prompt, true, 0.1, 'Expense Line Accounts');
            $decoded = $response ? json_decode($this->sanitizeJsonResponse($response), true) : null;
            foreach (($decoded['lines'] ?? []) as $m) {
                if (isset($m['index'], $m['account_id'])) {
                    $map[(int) $m['index']] = $accounts->firstWhere('id', (int) $m['account_id']);
                }
            }
        }
        foreach ($lines as $i => &$l) {
            $acc = $map[$i] ?? $fallback;
            $l['account_id'] = $acc?->id;
            $l['account_name'] = $acc ? "{$acc->code} - {$acc->name}" : null;
        }

        return $lines;
    }

    public function suggestExpenseCategory(Request $request, GeminiService $gemini): JsonResponse
    {
        $searchText = $request->input('description') . ' ' . $request->input('vendor');
        [$matched] = $this->resolveExpenseAccount($searchText, $gemini);
        
        return response()->json([
            'account_id' => $matched?->id,
            'account_name' => $matched ? "{$matched->code} - {$matched->name}" : null,
        ]);
    }

    private function resolveExpenseAccount(string $searchText, GeminiService $gemini): array
    {
        $accounts = Account::active()->where('is_posting', 1)->get(['id', 'code', 'name']);
        if ($accounts->isEmpty()) return [null, 'none'];

        $accountList = $accounts->map(fn ($a) => "{$a->id}: {$a->code} - {$a->name}")->implode("\n");
        $prompt = "Pick the best matching account from this list:\n{$accountList}\nFor: \"{$searchText}\"\nReturn ONLY JSON: {\"account_id\": <id>}";
        
        $response = $gemini->generateResponse($prompt, true, 0.1, 'Expense Category Suggestion');
        $decoded = $response ? json_decode($this->sanitizeJsonResponse($response), true) : null;
        
        $matched = ($decoded && isset($decoded['account_id'])) ? $accounts->firstWhere('id', (int) $decoded['account_id']) : null;
        return [$matched, $matched ? 'ai' : 'none'];
    }

    private function matchSupplier(string $vendorName): ?Supplier
    {
        return Supplier::whereRaw('LOWER(name_en) = ?', [strtolower($vendorName)])->first()
            ?? Supplier::where('name_en', 'like', '%' . $vendorName . '%')->first();
    }

    private function sanitizeJsonResponse(string $response): string
    {
        $response = trim($response);
        $response = preg_replace('/^```(?:json)?\s*/i', '', $response);
        $response = preg_replace('/\s*```$/', '', $response);
        $start = strpos($response, '{');
        $end = strrpos($response, '}');
        return ($start !== false && $end !== false) ? substr($response, $start, $end - $start + 1) : $response;
    }

    private function todayForPrompt(): string
    {
        return now()->format('Y-m-d');
    }
}
