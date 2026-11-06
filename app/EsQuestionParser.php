<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;
use Throwable;

class EsQuestionParser
{
    /** @return array{questions: array<int, array{question: string, answer: string}>, source: string, message: string} */
    public function parse(string $rawText): array
    {
        if (! config('services.gemini.api_key')) {
            return [
                'questions' => $this->splitLocally($rawText),
                'source' => 'local',
                'message' => 'Gemini未設定のため、簡易整理に切り替えました。結果を確認してください。',
            ];
        }

        try {
            return [
                'questions' => $this->parseWithGemini($rawText),
                'source' => 'gemini',
                'message' => 'Geminiで整理しました。内容を確認してください。',
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'questions' => $this->splitLocally($rawText),
                'source' => 'local',
                'message' => $this->fallbackMessage($exception),
            ];
        }
    }

    /** @return array<int, array{question: string, answer: string}> */
    private function parseWithGemini(string $rawText): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'questions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => ['type' => 'string'],
                            'answer' => ['type' => 'string'],
                        ],
                        'required' => ['question', 'answer'],
                    ],
                ],
            ],
            'required' => ['questions'],
        ];

        $prompt = "あなたは就職活動のES整理アシスタントです。以下の貼り付け文章から、企業の質問文と応募者の回答を順番どおりに抽出してJSONにしてください。質問や回答を創作・要約せず、判別できない箇所は原文を保ってください。\n\n<貼り付け文章>\n{$rawText}\n</貼り付け文章>";

        $model = config('services.gemini.model', 'gemini-3.5-flash-lite');

        try {
            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])
                ->connectTimeout(5)
                ->timeout(45)
                ->post('https://generativelanguage.googleapis.com/v1/interactions', [
                    'model' => $model,
                    'input' => $prompt,
                    'store' => false,
                    'response_format' => [
                        'type' => 'text',
                        'mime_type' => 'application/json',
                        'schema' => $schema,
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Gemini ES parse connection failed', [
                'model' => $model,
                'exception_class' => $exception::class,
            ]);

            throw new RuntimeException('Gemini APIへ接続できませんでした。');
        }

        if (! $response->successful()) {
            $providerError = $response->json('error', []);
            $providerError = is_array($providerError) ? $providerError : [];
            $retryAfter = $response->header('Retry-After');

            foreach (($providerError['details'] ?? []) as $detail) {
                if (is_array($detail)
                    && str_ends_with((string) ($detail['@type'] ?? ''), 'RetryInfo')
                    && is_string($detail['retryDelay'] ?? null)) {
                    $retryAfter ??= $detail['retryDelay'];
                }
            }

            $providerMessage = $providerError['message'] ?? null;
            if (is_string($providerMessage)) {
                $providerMessage = mb_substr(str_replace((string) config('services.gemini.api_key'), '[REDACTED]', $providerMessage), 0, 500);
            }

            Log::error('Gemini ES parse request rejected', [
                'model' => $model,
                'http_status' => $response->status(),
                'provider_code' => $providerError['code'] ?? $response->status(),
                'provider_status' => $providerError['status'] ?? null,
                'provider_message' => $providerMessage,
                'retry_after' => $retryAfter,
            ]);

            throw new RuntimeException('Gemini ES整理APIがHTTP '.$response->status().'で応答しました。');
        }

        $output = collect($response->json('steps', []))
            ->where('type', 'model_output')
            ->flatMap(fn (array $step): array => $step['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        try {
            $questions = json_decode($output, true, flags: JSON_THROW_ON_ERROR)['questions'] ?? [];
        } catch (Throwable $exception) {
            throw new RuntimeException('Geminiの整理結果を読み取れませんでした。再度お試しください。', previous: $exception);
        }

        $questions = collect($questions)
            ->filter(fn (mixed $question): bool => is_array($question) && filled($question['question'] ?? null))
            ->map(fn (array $question): array => [
                'question' => trim((string) $question['question']),
                'answer' => trim((string) ($question['answer'] ?? '')),
            ])
            ->values()
            ->all();

        if ($questions === []) {
            throw new RuntimeException('質問を見つけられませんでした。貼り付け内容を確認してください。');
        }

        return $questions;
    }

    private function fallbackMessage(Throwable $exception): string
    {
        for ($cause = $exception; $cause !== null; $cause = $cause->getPrevious()) {
            if (str_contains($cause->getMessage(), 'cURL error 60') || str_contains($cause->getMessage(), 'unable to get local issuer certificate')) {
                return 'PHPのSSL証明書設定が原因でGeminiへ接続できなかったため、簡易整理に切り替えました。';
            }
        }

        return 'Geminiで整理できなかったため、簡易整理に切り替えました。結果を確認してください。';
    }

    /** @return array<int, array{question: string, answer: string}> */
    private function splitLocally(string $rawText): array
    {
        $lines = preg_split('/\R/u', trim($rawText)) ?: [];
        $questionPattern = '/^(?:Q(?:uestion)?\s*\d*\s*[:：.)）-]?|質問\s*\d*\s*[:：.)）-]?|設問\s*\d*\s*[:：.)）-]?|問\s*\d+\s*[:：.)）-]?|【.{1,80}】|(?:学生時代|志望動機|志望理由|自己PR|自己紹介|あなたの強み|あなたの弱み|研究内容|ゼミ活動|課外活動|入社後|キャリアプラン|就職活動の軸|ガクチカ)(?:の|について|を|は|：|:|\s|（|$)|.{1,100}(?:教えてください|説明してください|述べてください|記入してください|書いてください|お答えください|理由を.{0,30}教えてください|[?？])\s*$)/iu';
        $segments = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match($questionPattern, $line) === 1) {
                if ($current !== null) {
                    $segments[] = $current;
                }
                $isNumberedHeading = preg_match('/^(?:Q(?:uestion)?|質問|設問|問)\s*\d+(?:\s|$|[:：.)）-])/iu', $line) === 1;
                $question = $isNumberedHeading
                    ? $line
                    : (preg_replace('/^(?:Q(?:uestion)?\s*\d*\s*[:：.)）-]?|質問\s*\d*\s*[:：.)）-]?|設問\s*\d*\s*[:：.)）-]?|問\s*\d+\s*[:：.)）-]?)\s*/iu', '', $line) ?: $line);
                $current = ['question' => $question, 'answer' => ''];
            } elseif ($current === null) {
                $current = ['question' => '質問の区切りを自動判定できませんでした', 'answer' => $line];
            } else {
                $current['answer'] .= ($current['answer'] === '' ? '' : "\n").$line;
            }
        }

        if ($current !== null) {
            $segments[] = $current;
        }

        $segments = array_map(function (array $segment): array {
            $question = trim((string) ($segment['question'] ?? ''));
            $answer = trim((string) ($segment['answer'] ?? ''));
            $isNumberedHeading = preg_match('/^(?:Q(?:uestion)?|質問|設問|問)\s*\d+(?:\s|$|[:：.)）-])/iu', $question) === 1;
            $combined = $isNumberedHeading ? $answer : trim($question."\n".$answer);
            $requestEnding = '(?:入力してください|入力して下さい|ご入力ください|記入してください|ご記入ください|記入して下さい|回答してください|お答えください|選択してください|教えてください|説明してください|述べてください|記載してください|お書きください|ご回答ください)';

            if (preg_match('/^(.+?'.$requestEnding.'[。．.!！]?)[\s]*([\s\S]*)$/u', $combined, $matches) === 1) {
                return ['question' => trim($matches[1]), 'answer' => trim($matches[2])];
            }

            if ($isNumberedHeading && $combined !== '') {
                return ['question' => $combined, 'answer' => ''];
            }

            return ['question' => $question, 'answer' => $answer];
        }, $segments);

        return $segments === []
            ? [['question' => '質問の区切りを自動判定できませんでした', 'answer' => trim($rawText)]]
            : $segments;
    }
}
