<?php

namespace App\Http\Controllers\Ocr;

use App\Http\Controllers\Controller;
use App\Models\Supplier\Supplier;
use Illuminate\Http\Request;
use Codesmiths\LaravelOcrSpace\Facades\OcrSpace;
use Codesmiths\LaravelOcrSpace\OcrSpaceOptions;
use Codesmiths\LaravelOcrSpace\Enums\Language;
use Codesmiths\LaravelOcrSpace\Enums\InputType;
use Codesmiths\LaravelOcrSpace\Enums\OcrSpaceEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Mockery\Exception;
use OpenAI;

class OcrController extends Controller
{
    public function index()
    {
        return view('ocr.test-ocr');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $file = $request->file('file');

        // Get the file's mime type and determine the OCR Space file type
        $mimeType = $file->getMimeType();
        $fileType = match (true) {
            str_contains($mimeType, 'pdf') => '.pdf',
            str_contains($mimeType, 'jpeg') || str_contains($mimeType, 'jpg') => '.jpg',
            str_contains($mimeType, 'png') => '.png',
            default => null
        };

        if (!$fileType) {
            // Fallback to extension if mime type doesn't match
            $extension = strtolower($file->getClientOriginalExtension());
            $fileType = match ($extension) {
                'pdf' => '.pdf',
                'jpg', 'jpeg' => '.jpg',
                'png' => '.png',
                default => null
            };
        }

        if (!$fileType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to recognize the file type'
            ], 400);
        }

        // Create options for OCR Space
        $options = OcrSpaceOptions::make()
            ->language(Language::English)
            ->fileType($fileType)
            ->detectOrientation(true)
            ->scale(true)
            ->isTable(true)
            ->ocrEngine(OcrSpaceEngine::Engine2);

        try {
            // Get the correct MIME type for the content type prefix
            $contentType = match (true) {
                str_contains($mimeType, 'pdf') => 'application/pdf',
                str_contains($mimeType, 'jpeg') || str_contains($mimeType, 'jpg') => 'image/jpeg',
                str_contains($mimeType, 'png') => 'image/png',
                default => 'application/octet-stream'
            };

            // Read the file content
            $fileContent = file_get_contents($file->getRealPath());

            // Create the properly formatted base64 string
            $base64Data = 'data:' . $contentType . ';base64,' . base64_encode($fileContent);

            // Process the file with OCR Space using base64 encoding
            $result = OcrSpace::parseImage(InputType::Base64, $base64Data, $options);

            // Get the parsed text
            $text = $result->getParsedResults()->first()->getParsedText();

            // Step 2: parse into structured JSON
            $structured = $this->processInvoice($text);

            //$structured = $this->parseInvoiceText($text);

            return response()->json([
                'status' => 'success',
                'structured_data' => $structured,
                'raw_text' => $text
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function processInvoiceWithChatGpt($rawOcrText)
    {
        $client = OpenAI::client(env('OPENAI_API_KEY'));

        // We explicitly name the missing fields in the prompt
        $descriptions = \App\Models\Master\Description::descriptions()->pluck('description', 'id');

        $suppliers = Supplier::suppliers()->mapWithKeys(function ($item) {
            // Encode the ID here using your custom function
            return [encodeId($item->id) => $item->name_en];
        });

// Convert the collection into a string for the Gemini Prompt
        $supplierListString = "";
        foreach ($suppliers as $encodedId => $name) {
            $supplierListString .= "- {$name}: {$encodedId}\n";
        }

        // Mapping for Gemini to follow
        $unitMapping = "
- Piece/PC: 1
- Kilogram/KG: 2
- Gram/G: 3
- Litre/LTR: 4
- Millilitre/ML: 5
- Meter/M: 6
- Centimeter/CM: 7
- Millimeter/MM: 8
- Box: 9
- Packet/PKT: 10
- Set: 11
- Dozen/DZ: 12
- Carton/CTN: 13
- Roll: 14
- Pair/PR: 15
";

        $prompt = "Extract invoice data from the text below.
Return ONLY valid JSON.
Date format: DD-MM-YYYY.

RULES FOR MAPPING:
1. 'supplier_id': Match name to ID from: [{$supplierListString}]. If no clear match, return null.
2. 'unit': Match to ID from: [{$unitMapping}]. Default to 1 if unsure.
3. 'description_id':
   - Match item/service to ID from: [{$descriptions}].
   - If the item on the invoice is NOT clearly in the provided list, return null.
   - DO NOT invent or guess an ID.
   - If description_id is null, still provide the raw name in the 'description' field.
    4.Vat_category must be 'STANDARD', 'ZERO', 'EXEMPT', or 'OUTOFSCOPE'.
    5.IMPORTANT FOR 'currency' FIELD:
Detect the currency (e.g., SAR, USD, INR, EUR). Use standard 3-letter ISO codes.
6. 'notes': Capture any Terms and Conditions, payment instructions, or general remarks found on the document.
    JSON Structure:
    {
      'supplier': { 'supplier_id':'', name': '', 'address': '', 'vat_no': '', 'cr_no': '', 'city': '', 'state': '', 'country': '', 'pincode': '' },
      'customer': { 'name': '', 'address': '', 'phone': '', 'vat_no': '', 'cr_no': '', 'city': '', 'state': '', 'country': '', 'pincode': '' },
      'invoice_details': { 'invoice_no': '', 'invoice_date': '', 'due_date': '','currency': '' },
      'items': [{
          'description_id':'','description': '', 'quantity': 0.00, 'unit':1, 'unit_price': 0.00, 'total_excl_vat': 0.00,
          'vat_percentage': 0, 'vat_amount': 0.00, 'vat_category': '', 'item_grand_total': 0.00
      }],
      'summary': { 'subtotal': 0.00, 'discount': 0.00, 'total_excl_vat': 0.00, 'total_vat': 0.00, 'grand_total': 0.00 },
      'bank_details': {'bank_name':'', 'account_no':'', 'account_name':'', 'ifsc_code':'', 'branch_name':''}
    }
    TEXT TO PARSE: " . $rawOcrText;

        $response = $client->chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $rawOcrText],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);
        $result = json_decode($response->choices[0]->message->content, true);
        dd($result);
        // 1. Check if the AI actually returned a candidate
        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            Log::error("Gemini Safety Block or Empty Response", ['result' => $result]);
            return null;
        }

        $jsonString = $result['candidates'][0]['content']['parts'][0]['text'];

        // 2. Clean common AI formatting issues (sometimes they wrap in ```json ... ```)
        $jsonString = preg_replace('/^```json\s*|```$/m', '', $jsonString);
        $jsonString = trim($jsonString);

        $decoded = json_decode($jsonString, true);

        // 3. Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON Decode Failed: " . json_last_error_msg(), ['string' => $jsonString]);
            return null;
        }

        return $decoded;
    }


    /**
     * Check if a value is in the list of invalid keywords
     *
     * @param string $value The value to check
     * @param array $invalidKeywords List of invalid keywords
     * @return bool True if the value is invalid
     */
    /*private function isInvalidValue($value, $invalidKeywords)
    {
        if (empty($value)) {
            return true;
        }

        // Check if the value contains any of the invalid keywords
        foreach ($invalidKeywords as $keyword) {
            if (stripos($value, $keyword) !== false) {
                return true;
            }
        }

        // Check if the value is just a number or very short
        if (is_numeric($value) || strlen($value) < 2) {
            return true;
        }

        // Check if the value contains too many special characters or numbers
        $specialCharCount = preg_match_all('/[^a-zA-Z\s]/', $value, $matches);
        $totalLength = strlen($value);
        if ($specialCharCount > $totalLength / 2) {
            return true;
        }

        return false;
    }*/

    /**
     * Display the Google OCR test view
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function googleOcrIndex()
    {
        return view('ocr.test-google-ocr');
    }

    /**
     * Process file upload with Google OCR
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function googleOcrUpload(Request $request)
    {
        // Prevent spam (5 attempts per minute per user)
        $executed = RateLimiter::attempt('ocr:' . auth()->id(), 5, function () {
        });
        if (!$executed) {
            return response()->json(['status' => 'error', 'message' => 'Too many requests.'], 429);
        }

        $request->validate(['file' => 'required|mimes:pdf,jpg,jpeg,png|max:20480']);
        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $base64Data = base64_encode(file_get_contents($file->getRealPath()));
        $apiKey = env('GOOGLE_CLOUD_API_KEY');

        // Determine endpoint and structure based on file type
        if ($mimeType === 'application/pdf') {
            $url = "https://vision.googleapis.com/v1/files:annotate?key={$apiKey}";
            $requestBody = [
                'requests' => [[
                    'inputConfig' => [
                        'content' => $base64Data,
                        'mimeType' => 'application/pdf'
                    ],
                    'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']],
                    // Process the first few pages (1 to 5)
                    'pages' => [1, 2, 3]
                ]]
            ];
        } else {
            $url = "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}";
            $requestBody = [
                'requests' => [[
                    'image' => ['content' => $base64Data],
                    'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']]
                ]]
            ];
        }

        $response = Http::post($url, $requestBody);

        if ($response->successful()) {
            $data = $response->json();

            // Extract text (PDF response structure is deeper than Image)
            $text = '';
            if ($mimeType === 'application/pdf') {
                // PDF returns an array of responses (one per page)
                foreach ($data['responses'][0]['responses'] ?? [] as $page) {
                    $text .= ($page['fullTextAnnotation']['text'] ?? '') . "\n";
                }
            } else {
                $text = $data['responses'][0]['fullTextAnnotation']['text'] ?? '';
            }
            $optimizedText = $this->cleanOcrText($text);
            //$structured = $this->processInvoiceWithChatGpt($optimizedText);
            //dd($structured);
            $structured = $this->processInvoiceWithGemini($optimizedText);
            if ($structured) {
                return response()->json([
                    'status' => 'success',
                    'structured_data' => $structured,
                    'raw_text' => $optimizedText
                ]);
            }
            return errorResponse('Limit Exceeded. Try After Sometime');
        }

        return response()->json(['status' => 'error', 'message' => $response->body()], 500);
    }

    private function cleanOcrText($text)
    {
        // 1. Remove non-printable characters and weird symbols
        $text = preg_replace('/[^\x20-\x7E\n]/', '', $text);

        // 2. Collapse multiple spaces into one
        $text = preg_replace('/ +/', ' ', $text);

        // 3. Remove empty lines
        $text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);

        return trim($text);
    }

    private function processInvoiceWithGemini($text)
    {


        $descriptions = \App\Models\Master\Description::descriptions()->pluck('description', 'id');

        $suppliers = Supplier::suppliers()->mapWithKeys(function ($item) {
            // Encode the ID here using your custom function
            return [encodeId($item->id) => $item->name_en];
        });

// Convert the collection into a string for the Gemini Prompt
        $supplierListString = "";
        foreach ($suppliers as $encodedId => $name) {
            $supplierListString .= "- {$name}: {$encodedId}\n";
        }

        // Mapping for Gemini to follow
        $unitMapping = "
- Piece/PC: 1
- Kilogram/KG: 2
- Gram/G: 3
- Litre/LTR: 4
- Millilitre/ML: 5
- Meter/M: 6
- Centimeter/CM: 7
- Millimeter/MM: 8
- Box: 9
- Packet/PKT: 10
- Set: 11
- Dozen/DZ: 12
- Carton/CTN: 13
- Roll: 14
- Pair/PR: 15
";

        $prompt = "Extract invoice data from the text below.
Return ONLY valid JSON.
Date format: DD-MM-YYYY.

RULES FOR MAPPING:
1. 'supplier_id': Match name to ID from: [{$supplierListString}]. If no clear match, return null.
2. 'unit': Match to ID from: [{$unitMapping}]. Default to 1 if unsure.
3. 'description_id':
   - Match item/service to ID from: [{$descriptions}].
   - If the item on the invoice is NOT clearly in the provided list, return null.
   - DO NOT invent or guess an ID.
   - If description_id is null, still provide the raw name in the 'description' field.
    4.Vat_category must be 'STANDARD', 'ZERO', 'EXEMPT', or 'OUTOFSCOPE'.
    5.IMPORTANT FOR 'currency' FIELD:
Detect the currency (e.g., SAR, USD, INR, EUR). Use standard 3-letter ISO codes.
6. 'notes': Capture any Terms and Conditions, payment instructions, or general remarks found on the document.
    JSON Structure:
    {
      'supplier': { 'supplier_id':'', name': '', 'address': '', 'vat_no': '', 'cr_no': '', 'city': '', 'state': '', 'country': '', 'pincode': '' },
      'customer': { 'name': '', 'address': '', 'phone': '', 'vat_no': '', 'cr_no': '', 'city': '', 'state': '', 'country': '', 'pincode': '' },
      'invoice_details': { 'invoice_no': '', 'invoice_date': '', 'due_date': '','currency': '' },
      'items': [{
          'description_id':'','description': '', 'quantity': 0.00, 'unit':1, 'unit_price': 0.00, 'total_excl_vat': 0.00,
          'vat_percentage': 0, 'vat_amount': 0.00, 'vat_category': '', 'item_grand_total': 0.00
      }],
      'summary': { 'subtotal': 0.00, 'discount': 0.00, 'total_excl_vat': 0.00, 'total_vat': 0.00, 'grand_total': 0.00 },
      'bank_details': {'bank_name':'', 'account_no':'', 'account_name':'', 'ifsc_code':'', 'branch_name':''}
    }
    TEXT TO PARSE: " . $text;

        $geminiKey = env('GEMINI_API_KEY');
        // Ensure you are using v1beta for 'response_mime_type' support
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$geminiKey}";

        // Attempt 1: Primary Model (Gemini 2.5 Flash)
        $response = Http::post($url, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.1, // Low temperature for more consistent JSON
            ]
        ]);


        if ($response->status() === 429) {
            $data = $response->json();
            if ($data['error']['status'] === 'RESOURCE_EXHAUSTED') {
                $message = $data['error']['message'] ?? 'No message provided';

                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key={$geminiKey}";
                $response = Http::post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                        'temperature' => 0.1, // Low temperature for more consistent JSON
                    ]
                ]);
                if ($response->status() === 503) {
                    return $message;
                }
            }

        }


        if ($response->successful()) {
            $result = $response->json();

            // 1. Check if the AI actually returned a candidate
            if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error("Gemini Safety Block or Empty Response", ['result' => $result]);
                return null;
            }

            $jsonString = $result['candidates'][0]['content']['parts'][0]['text'];

            // 2. Clean common AI formatting issues (sometimes they wrap in ```json ... ```)
            $jsonString = preg_replace('/^```json\s*|```$/m', '', $jsonString);
            $jsonString = trim($jsonString);

            $decoded = json_decode($jsonString, true);

            // 3. Check for JSON parsing errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON Decode Failed: " . json_last_error_msg(), ['string' => $jsonString]);
                return null;
            }

            return $decoded;
        }

        Log::error("Gemini API Connection Failed: " . $response->body());
        return null;
    }
}
