<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocalAiService
{
    public function getAiOutput(array $messages, string $model = 'google/gemma-3-1b', float $temperature = 0.7, int $maxTokens = -1, bool $stream = false): ?string
    {
        try {
            $response = Http::post(env('LOCAL_AI_URL', 'http://localhost:1234/v1/chat/completions'), [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'stream' => $stream,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['choices'][0]['message']['content'])) {
                    return $json['choices'][0]['message']['content'];
                } else {
                    Log::error('LocalAi API response missing expected structure', [
                        'response' => $response->body(),
                    ]);
                    return null;
                }
            }

            Log::error('LocalAi API request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('LocalAi API request exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
