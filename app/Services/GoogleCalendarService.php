<?php

namespace App\Services;

use App\Http\Controllers\ScheduleController;
use App\Models\ScheduleEvent;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleCalendarService
{
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
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
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
        $payload = $this->eventPayload($event);
        $request = Http::withToken($accessToken)->acceptJson();

        $response = $event->google_calendar_event_id
            ? $request->patch($url.'/'.rawurlencode($event->google_calendar_event_id).'?sendUpdates=none', $payload)
            : $request->post($url.'?sendUpdates=none', $payload);

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
    private function eventPayload(ScheduleEvent $event): array
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
            'colorId' => $event->confirmation_status === 'confirmed' ? '11' : '8',
            'start' => ['dateTime' => $start->toRfc3339String(), 'timeZone' => 'Asia/Tokyo'],
            'end' => ['dateTime' => $end->toRfc3339String(), 'timeZone' => 'Asia/Tokyo'],
            'reminders' => ['useDefault' => false, 'overrides' => []],
        ];

        if ($event->meeting_format === 'offline' && $event->location) {
            $payload['location'] = $event->location;
        }

        return $payload;
    }
}
