<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Expense\Expense;
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
Extract from receipt: vendor_name, expense_date(YYYY-MM-DD), subtotal, tax_amount, tax_rate, currency, category, description.
Return ONLY JSON.
PROMPT;

        $response = $gemini->generateVisionResponse($prompt, $base64, $file->getMimeType(), true, null, 'Scan Expense Receipt');
        if (!$response) return errorResponse('AI extraction failed.');

        $decoded = json_decode($this->sanitizeJsonResponse($response), true);
        $vendorName = trim((string) ($decoded['vendor_name'] ?? ''));
        $matchedSupplier = $vendorName ? $this->matchSupplier($vendorName) : null;
        
        $searchText = trim($vendorName . ' ' . ($decoded['category'] ?? '') . ' ' . ($decoded['description'] ?? ''));
        [$matchedAccount] = $this->resolveExpenseAccount($searchText, $gemini);

        return response()->json([
            'vendor_name' => $vendorName,
            'supplier_id' => $matchedSupplier ? encodeId($matchedSupplier->id) : null,
            'supplier_name' => $matchedSupplier?->name_en,
            'expense_date' => $decoded['expense_date'] ?? now()->format('Y-m-d'),
            'subtotal' => (float) ($decoded['subtotal'] ?? 0),
            'tax_amount' => (float) ($decoded['tax_amount'] ?? 0),
            'tax_rate' => (float) ($decoded['tax_rate'] ?? 0),
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
        ]);

        $existing = Supplier::where('company_id', companyId())
            ->where(fn($q) => $q->whereRaw('LOWER(email) = ?', [strtolower($data['email'] ?? '')])
                ->orWhereRaw('LOWER(name_en) = ?', [strtolower($data['name'])]))
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'success',
                'supplier_id' => encodeId($existing->id),
                'party' => ['id' => encodeId($existing->id), 'name' => $existing->name_en],
            ]);
        }

        $supplier = Supplier::create([
            'company_id' => companyId(),
            'name_en' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => 'active',
            'unique_row_no' => generateUniqueRowNo(Supplier::class, 'created_at', now(), 'supplier'),
            'row_no' => SequenceService::generateRowNo('supplier', 'SCN'),
        ]);

        return response()->json([
            'status' => 'success',
            'supplier_id' => encodeId($supplier->id),
            'party' => ['id' => encodeId($supplier->id), 'name' => $supplier->name_en],
        ]);
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
