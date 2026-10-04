<?php

namespace App\Services;

use App\Exceptions\AiTokenLimitExceededException;
use App\Models\Billing\AiUsage;
use App\Models\Master\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper over the Gemini REST API.
 *
 * Two entry points: generateResponse() for text-only calls, and
 * generateVisionResponse() for image+text calls (receipt/bill scanning).
 *
 * Everything funnels through callGemini(), which is the single place that
 * enforces the per-company token quota, retries Gemini's transient overload
 * responses, and records usage — so no individual call site has to know about
 * any of those concerns.
 */
class GeminiService
{
    protected string $apiKey;

    protected string $apiBase = 'https://generativelanguage.googleapis.com/v1beta/models/';

    protected string $defaultModel = 'gemini-3.5-flash-lite';

    /**
     * Document scanning is the one call worth tuning per-deployment: a shared
     * AI Studio key can lose access to a model overnight, or the currently
     * active model can be temporarily overloaded. So the scanning model is a
     * GEMINI_SCAN_QUALITY setting rather than a hardcoded constant.
     */
    protected function scanModel(): string
    {
        return match (strtolower((string) config('services.gemini.scan_quality', 'medium'))) {
            'high' => 'gemini-3.8-flash',
            default => 'gemini-3.8-flash',
        };
    }

    public function __construct()
    {
        // Read from config rather than env() directly, so this still works
        // after `php artisan config:cache`.
        $this->apiKey = (string) config('services.gemini.key', '');
    }

    public function generateResponse(string $prompt, bool $isJson = false, float $temperature = 0.7, ?string $feature = null): ?string
    {
        if ($this->apiKey === '') {
            Log::warning('Gemini API key is not set.');

            return null;
        }

        $generationConfig = [
            'temperature' => $temperature,
            'topP' => 0.95,
            'maxOutputTokens' => 4096,
        ];

        if ($isJson) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        return $this->callGemini([
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => $generationConfig,
        ], 'Gemini', $this->defaultModel, $feature);
    }

    /**
     * Multimodal call — an image/PDF plus a text prompt ("extract these fields
     * from this receipt"). Same request shape as generateResponse(), with an
     * extra inline_data part next to the text part.
     *
     * $responseSchema (used only when $isJson) is a Gemini structured-output
     * schema (OpenAPI subset: {type, properties, required}). This is stricter
     * than responseMimeType alone — it constrains the reply server-side to that
     * exact shape, ruling out renamed/missing/mistyped fields instead of relying
     * on the caller to sanitise whatever prose-wrapped JSON comes back.
     * See https://ai.google.dev/gemini-api/docs/structured-output
     */
    public function generateVisionResponse(string $prompt, string $base64Data, string $mimeType, bool $isJson = false, ?array $responseSchema = null, ?string $feature = null): ?string
    {
        if ($this->apiKey === '') {
            Log::warning('Gemini API key is not set.');

            return null;
        }

        $generationConfig = [
            // Low temperature: this is data extraction, not creative writing.
            'temperature' => 0.2,
            'topP' => 0.95,
            'maxOutputTokens' => 8192,
        ];

        if ($isJson) {
            $generationConfig['responseMimeType'] = 'application/json';

            if ($responseSchema !== null) {
                $generationConfig['responseSchema'] = $responseSchema;
            }
        }

        return $this->callGemini([
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Data]],
                ],
            ]],
            'generationConfig' => $generationConfig,
        ], 'Gemini Vision', $this->scanModel(), $feature);
    }

    /**
     * Shared HTTP call + retry logic. The free/shared-capacity tier returns 503
     * "high demand" and 429 fairly often — transient, not real failures, so
     * worth a couple of short-backoff retries before telling the user to fill
     * the form manually.
     */
    private function callGemini(array $payload, string $logLabel, string $model, ?string $feature = null): ?string
    {
        $company = $this->currentCompany();

        // AI is opt-in, not opt-out: a company needs a real, positive token limit
        // before it can use AI at all. An unconfigured (null) limit is treated the
        // same as an exhausted one — both mean "buy AI tokens", not "unlimited".
        if ($company) {
            $limit = (int) ($company->ai_token_limit ?? 0);
            $used = (int) ($company->ai_tokens_used ?? 0);

            if ($limit <= 0 || $used >= $limit) {
                throw new AiTokenLimitExceededException();
            }

            // A company can have a few tokens left but not enough to finish this
            // request — letting the call fail only after the fact is confusing
            // ("why did it say I'm out now?"). We already have the prompt about
            // to be sent, so estimate its real input size directly (~4 chars per
            // token for English) and add 10% for the reply.
            $remaining = $limit - $used;
            $estimatedInput = $this->estimateInputTokens($payload);
            $estimatedNeed = $estimatedInput + (int) ceil($estimatedInput / 10);

            if ($remaining < $estimatedNeed) {
                throw new AiTokenLimitExceededException(
                    'Your AI token balance is low — you have ' . $this->fmt($remaining) . ' tokens left, '
                    . 'but this request typically needs about ' . $this->fmt($estimatedNeed) . ' tokens. '
                    . 'Please contact your admin to buy more tokens.'
                );
            }
        }

        // HTTP-status retries (503/429) fail fast, so 3 attempts is cheap. A
        // connection timeout costs a full 60s per attempt, so it only gets one
        // retry — enough to survive a one-off network blip without risking
        // minutes of a user's request hanging if Gemini is genuinely down.
        $maxStatusAttempts = 3;
        $maxTimeoutAttempts = 2;
        $lastStatus = null;
        $lastBody = null;
        $timeoutAttempt = 0;
        $url = $this->apiBase . $model . ':generateContent';

        for ($attempt = 1; $attempt <= $maxStatusAttempts; $attempt++) {
            try {
                $response = Http::timeout(60)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url . '?key=' . $this->apiKey, $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                    if (! $text) {
                        Log::warning("{$logLabel} returned empty text.", ['response' => $data]);
                    }

                    $this->recordUsage($company, $data, $feature ?? $logLabel);

                    return $text;
                }

                $lastStatus = $response->status();
                $lastBody = $response->body();

                // 503 (overloaded) and 429 (rate limited) are the only statuses
                // worth retrying — anything else (bad key, malformed request)
                // fails identically every time.
                if (! in_array($lastStatus, [503, 429], true) || $attempt === $maxStatusAttempts) {
                    break;
                }

                usleep($attempt * 500_000); // 0.5s, 1.0s backoff
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $timeoutAttempt++;

                if ($timeoutAttempt >= $maxTimeoutAttempts) {
                    Log::error("{$logLabel} Service Exception (timeout, gave up after {$timeoutAttempt} attempts): " . $e->getMessage());

                    return null;
                }

                Log::warning("{$logLabel} timed out, retrying once: " . $e->getMessage());
            } catch (AiTokenLimitExceededException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error("{$logLabel} Service Exception: " . $e->getMessage());

                return null;
            }
        }

        Log::error("{$logLabel} API HTTP error", [
            'model' => $model,
            'status' => $lastStatus,
            'body' => $lastBody,
        ]);

        return null;
    }

    /**
     * Bump the company's running total and log the per-request breakdown.
     * Direct assignment + save, not update([...]) — Company declares no
     * $fillable, so Eloquent would reject the mass assignment.
     */
    private function recordUsage(?Company $company, array $data, string $feature): void
    {
        if (! $company) {
            return;
        }

        $inputTokens = (int) ($data['usageMetadata']['promptTokenCount'] ?? 0);
        $outputTokens = (int) ($data['usageMetadata']['candidatesTokenCount'] ?? 0);
        $totalTokens = $inputTokens + $outputTokens;

        if ($totalTokens <= 0) {
            return;
        }

        $company->ai_tokens_used = (int) ($company->ai_tokens_used ?? 0) + $totalTokens;
        $company->save();

        AiUsage::create([
            'company_id' => $company->id,
            'user_id' => auth()->id(),
            'feature' => $feature,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
        ]);
    }

    private function currentCompany(): ?Company
    {
        return Company::currentFresh();
    }

    /**
     * ~4 characters per token is the standard rough rule of thumb for English
     * text. There's no way to count exactly without calling the API, which is
     * the very thing this estimate exists to avoid doing when the balance is
     * already too low.
     */
    private function estimateInputTokens(array $payload): int
    {
        $text = '';

        foreach ($payload['contents'][0]['parts'] ?? [] as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'];
            }
        }

        return (int) ceil(mb_strlen($text) / 4);
    }

    private function fmt($amount): string
    {
        return number_format((float) $amount);
    }
}
