<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CompanyResearchAssistant
{
    /** @param array<int, array{role: string, text: string}> $history
     * @return array{answer: string, sources: array<int, array{title: string, url: string}>}
     */
    public function reply(string $company, string $message, array $history): array
    {
        $transcript = collect($history)->take(-12)->map(fn (array $item): string => ($item['role'] === 'assistant' ? 'AI' : '利用者').'：'.$item['text'])->implode("\n");
        $prompt = "あなたは日本の新卒就活を支援する企業研究アシスタントです。Google検索の最新情報を使い、事実と推測を分け、重要な情報には根拠のURLを示してください。給与・休日・制度などは公式採用ページや公式資料を優先し、見つからない値を推測で埋めず「公開情報では確認できません」と答えてください。個人情報や応募者の秘密は質問しないでください。\n\n対象企業：{$company}\nこれまでの会話：\n{$transcript}\n\n利用者の質問：\n{$message}";

        $response = $this->interact($prompt);
        $content = $this->modelContent($response);
        $answer = $content->where('type', 'text')->pluck('text')->implode("\n\n");
        if (trim($answer) === '') {
            throw new RuntimeException('Geminiから回答を取得できませんでした。');
        }

        return ['answer' => trim($answer), 'sources' => $this->citations($content)];
    }

    /** @return array<string, mixed> */
    public function summarizeCompensation(string $company): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'initial_salary' => ['type' => 'string'],
                'salary_structure' => ['type' => 'string'],
                'bonus_and_raise' => ['type' => 'string'],
                'benefits' => ['type' => 'string'],
                'holidays' => ['type' => 'string'],
                'work_location' => ['type' => 'string'],
                'work_hours' => ['type' => 'string'],
                'notes' => ['type' => 'string'],
            ],
            'required' => ['initial_salary', 'salary_structure', 'bonus_and_raise', 'benefits', 'holidays', 'work_location', 'work_hours', 'notes'],
        ];
        $prompt = "Google検索を使い、{$company}の新卒採用情報から初任給・給与内訳・賞与昇給・福利厚生・休日・勤務地・勤務時間を短く整理してください。できる限り公式採用サイトや募集要項を優先し、金額には対象年度と対象区分を含めます。情報が見つからない項目は必ず「公開情報では確認できません」とし、推測で補わないでください。回答は指定JSONのみ。";
        $response = $this->interact($prompt, ['response_format' => ['type' => 'text', 'mime_type' => 'application/json', 'schema' => $schema]]);
        $content = $this->modelContent($response);
        $text = $content->where('type', 'text')->pluck('text')->implode('');

        try {
            $summary = json_decode($text, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new RuntimeException('企業待遇の要約を読み取れませんでした。', previous: $exception);
        }

        return ['summary' => $summary, 'sources' => $this->citations($content)];
    }

    /** @return array<string, mixed> */
    private function interact(string $prompt, array $extra = []): array
    {
        $apiKey = config('services.gemini.api_key');
        if (! $apiKey) {
            throw new RuntimeException('Gemini APIキーが未設定です。');
        }

        $model = config('services.gemini.model', 'gemini-3.5-flash-lite');

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->connectTimeout(5)
                ->timeout(60)
                ->post('https://generativelanguage.googleapis.com/v1/interactions', [
                    'model' => $model,
                    'input' => $prompt,
                    'store' => false,
                    'tools' => [['type' => 'google_search']],
                    ...$extra,
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Gemini API connection failed', [
                'model' => $model,
                'exception_class' => $exception::class,
            ]);

            throw new RuntimeException('Gemini APIへ接続できませんでした。時間をおいて再試行してください。');
        }

        if (! $response->successful()) {
            $providerError = $response->json('error', []);
            $providerError = is_array($providerError) ? $providerError : [];
            $retryAfter = $response->header('Retry-After');
            $quotaFailures = [];

            foreach (($providerError['details'] ?? []) as $detail) {
                if (! is_array($detail)) {
                    continue;
                }

                $type = (string) ($detail['@type'] ?? '');
                if (str_ends_with($type, 'RetryInfo') && is_string($detail['retryDelay'] ?? null)) {
                    $retryAfter ??= $detail['retryDelay'];
                }

                if (str_ends_with($type, 'QuotaFailure')) {
                    foreach (($detail['violations'] ?? []) as $violation) {
                        if (! is_array($violation)) {
                            continue;
                        }

                        $quotaFailures[] = array_filter([
                            'quota_metric' => $violation['quotaMetric'] ?? null,
                            'quota_id' => $violation['quotaId'] ?? null,
                            'quota_value' => $violation['quotaValue'] ?? null,
                            'model' => $violation['quotaDimensions']['model'] ?? null,
                            'location' => $violation['quotaDimensions']['location'] ?? null,
                        ], fn (mixed $value): bool => $value !== null);
                    }
                }
            }

            $providerMessage = $providerError['message'] ?? null;
            if (is_string($providerMessage)) {
                $providerMessage = mb_substr(str_replace($apiKey, '[REDACTED]', $providerMessage), 0, 500);
            }

            Log::error('Gemini API request rejected', [
                'model' => $model,
                'tool' => 'google_search',
                'http_status' => $response->status(),
                'provider_code' => $providerError['code'] ?? $response->status(),
                'provider_status' => $providerError['status'] ?? null,
                'provider_message' => $providerMessage,
                'retry_after' => $retryAfter,
                'quota_failures' => $quotaFailures,
            ]);
        }

        if ($response->status() === 429) {
            throw new RuntimeException('Geminiの利用上限（無料枠・時間あたり・日あたり等）に達しています。Google AI StudioのRate limitsで、このプロジェクトのモデルと検索利用枠を確認してください。課金の設定はせず、まず利用枠の表示を確認してください。');
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new RuntimeException('Gemini APIキーまたはプロジェクトの利用権限を確認してください。Google AI StudioのAPI Keysとプロジェクト状態を確認してください。');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Gemini APIがHTTP '.$response->status().'で応答しました。Renderログの安全な診断情報を確認してください。');
        }

        $body = $response->json();
        if (! is_array($body)) {
            throw new RuntimeException('Gemini APIから有効なJSON応答を取得できませんでした。');
        }

        return $body;
    }

    /** @param array<string, mixed> $response
     * @return Collection<int, array<string, mixed>>
     */
    private function modelContent(array $response): Collection
    {
        return collect($response['steps'] ?? [])
            ->where('type', 'model_output')
            ->flatMap(fn (array $step): array => $step['content'] ?? [])
            ->values();
    }

    /** @param Collection<int, array<string, mixed>> $content
     * @return array<int, array{title: string, url: string}>
     */
    private function citations(Collection $content): array
    {
        return $content
            ->flatMap(fn (array $item): array => $item['annotations'] ?? [])
            ->filter(fn (array $annotation): bool => in_array($annotation['type'] ?? '', ['url_citation', 'urlCitation'], true))
            ->map(fn (array $annotation): array => [
                'title' => (string) ($annotation['title'] ?? $annotation['url'] ?? $annotation['uri'] ?? '参照元'),
                'url' => (string) ($annotation['url'] ?? $annotation['uri'] ?? ''),
            ])
            ->filter(fn (array $source): bool => str_starts_with($source['url'], 'https://') || str_starts_with($source['url'], 'http://'))
            ->unique('url')
            ->values()
            ->all();
    }
}
