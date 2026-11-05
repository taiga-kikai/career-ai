<?php

namespace App\Services;

use App\Http\Controllers\ScheduleController;
use App\Models\ScheduleEvent;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleCalendarService
{
    private const CALENDAR_COLOR_LABELS = [
        'eucalyptus' => ['id' => '4bd59069-a482-4b2f-a502-6d620b64002e', 'name' => 'ユーカリ', 'color' => '#009688', 'aliases' => ['ユーカリ', 'eucalyptus']],
        'tomato' => ['id' => '8a8e78b5-5f65-4748-a21b-659c2d76b132', 'name' => 'トマト', 'color' => '#d50000', 'aliases' => ['トマト', 'tomato']],
        'graphite' => ['id' => '167839c0-66d8-4ff6-b37d-a957a65af840', 'name' => 'グラファイト', 'color' => '#616161', 'aliases' => ['グラファイト', 'graphite']],
    ];

    public function isConfigured(): bool
    {
        return filled(config('services.google_calendar.client_id'))
            && filled(config('services.google_calendar.client_secret'))
            && filled(config('services.google_calendar.redirect_uri'));
    }

    public function authorizationUrl(string $state): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Google Calendarの認証設定がまだ完了していません。');
        }

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google_calendar.client_id'),
            'redirect_uri' => config('services.google_calendar.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/calendar.calendars',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /** @return array<string, mixed> */
    public function exchangeAuthorizationCode(string $code): array
    {
        $response = Http::asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'redirect_uri' => config('services.google_calendar.redirect_uri'),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Googleの認証に失敗しました。認証設定を確認して、もう一度連携してください。');
        }

        return $response->json();
    }

    /** @return array{id: string, htmlLink: string|null} */
    public function syncEvent(User $user, ScheduleEvent $event): array
    {
        $accessToken = $this->refreshAccessToken($user);
        $url = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';
        $isDeadline = in_array($event->event_type, ['es_deadline', 'aptitude_deadline'], true);
        $labels = $this->ensureCalendarColorLabels($accessToken);
        $colorLabel = $isDeadline ? 'eucalyptus' : ($event->confirmation_status === 'confirmed' ? 'tomato' : 'graphite');
        $payload = $this->eventPayload($event, $labels[$colorLabel]);
        $request = Http::withToken($accessToken)->acceptJson();
        $query = '?sendUpdates=none&eventLabelVersion=1';

        $response = $event->google_calendar_event_id
            ? $request->patch($url.'/'.rawurlencode($event->google_calendar_event_id).$query, $payload)
            : $request->post($url.$query, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Googleカレンダーへの保存に失敗しました。連携状態を確認して、もう一度お試しください。');
        }

        $data = $response->json();

        if (! is_array($data) || ! is_string($data['id'] ?? null)) {
            throw new RuntimeException('Googleカレンダーから予定情報を受け取れませんでした。');
        }

        return ['id' => $data['id'], 'htmlLink' => is_string($data['htmlLink'] ?? null) ? $data['htmlLink'] : null];
    }

    public function revoke(User $user): void
    {
        $refreshToken = $user->google_calendar_refresh_token;

        if (! is_string($refreshToken) || $refreshToken === '') {
            return;
        }

        Http::asForm()->post('https://oauth2.googleapis.com/revoke', ['token' => $refreshToken]);
    }

    private function refreshAccessToken(User $user): string
    {
        $refreshToken = $user->google_calendar_refresh_token;

        if (! is_string($refreshToken) || $refreshToken === '') {
            throw new RuntimeException('Googleカレンダーが未連携です。先にGoogleアカウントを連携してください。');
        }

        $response = Http::asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        $accessToken = $response->json('access_token');

        if (! $response->successful() || ! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Googleとの連携が切れています。Googleカレンダーを再連携してください。');
        }

        return $accessToken;
    }

    /** @return array<string, mixed> */
    private function eventPayload(ScheduleEvent $event, string $eventLabelId): array
    {
        $start = $event->starts_at->timezone('Asia/Tokyo');
        $isDeadline = in_array($event->event_type, ['es_deadline', 'aptitude_deadline'], true);
        $end = $event->ends_at?->timezone('Asia/Tokyo') ?? $start->copy()->addMinutes($isDeadline ? 15 : 60);
        $type = ScheduleController::eventTypeNames()[$event->event_type] ?? '予定';
        $activity = $event->title && $event->title !== $type ? $type.'（'.$event->title.'）' : $type;
        $time = $isDeadline ? '~'.$start->format('H:i') : $start->format('H:i').'~'.$end->format('H:i');
        $description = collect([
            $event->selection_type === 'main' ? '本選考' : 'インターン',
            match ($event->meeting_format) {
                'online' => '開催形式：オンライン',
                'offline' => '開催形式：オフライン',
                default => null,
            },
            $event->notes,
            $event->meeting_url ? '参加URL：'.$event->meeting_url : null,
        ])->filter()->implode("\n\n");
        $payload = [
            'summary' => implode('　', array_filter([$time, $activity, $event->company_name])),
            'description' => $description,
            'start' => ['dateTime' => $start->toRfc3339String(), 'timeZone' => 'Asia/Tokyo'],
            'end' => ['dateTime' => $end->toRfc3339String(), 'timeZone' => 'Asia/Tokyo'],
            'reminders' => ['useDefault' => false, 'overrides' => []],
        ];

        $payload['eventLabelId'] = $eventLabelId;

        if ($event->meeting_format === 'offline' && $event->location) {
            $payload['location'] = $event->location;
        }

        return $payload;
    }

    /** @return array<string, string> */
    private function ensureCalendarColorLabels(string $accessToken): array
    {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/primary';
        $request = Http::withToken($accessToken)->acceptJson();
        $calendarResponse = $request->get($url.'?fields=labelProperties');

        if (! $calendarResponse->successful()) {
            throw new RuntimeException('Googleカレンダーの色ラベルを確認できませんでした。カレンダーを再連携してください。');
        }

        $labelProperties = $calendarResponse->json('labelProperties', []);
        $labels = is_array($labelProperties['eventLabels'] ?? null) ? $labelProperties['eventLabels'] : [];

        $labelIds = [];
        foreach ($labels as $label) {
            if (! is_array($label)) {
                continue;
            }

            $name = mb_strtolower((string) ($label['name'] ?? ''));
            foreach (self::CALENDAR_COLOR_LABELS as $key => $definition) {
                if (in_array($name, $definition['aliases'], true)) {
                    $labelIds[$key] = (string) ($label['id'] ?? '');
                }
            }
        }

        $changed = false;
        foreach (self::CALENDAR_COLOR_LABELS as $key => $definition) {
            if (filled($labelIds[$key] ?? null)) {
                continue;
            }

            $labels[] = [
                'id' => $definition['id'],
                'backgroundColor' => $definition['color'],
                'name' => $definition['name'],
            ];
            $labelIds[$key] = $definition['id'];
            $changed = true;
        }

        if ($changed) {
            $updateResponse = $request->patch($url, [
                'labelProperties' => ['eventLabels' => $labels],
            ]);

            if (! $updateResponse->successful()) {
                throw new RuntimeException('Googleカレンダーに色ラベルを設定できませんでした。連携を更新してもう一度お試しください。');
            }
        }

        return $labelIds;
    }
}
