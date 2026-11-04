<?php

namespace App\Http\Controllers;

use App\Models\ScheduleEvent;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class GoogleCalendarController extends Controller
{
    public function connect(Request $request, GoogleCalendarService $calendar): RedirectResponse
    {
        $this->ensureLocalPreview();
        $this->ensureCalendarAccess($request);

        if (! $calendar->isConfigured()) {
            return to_route('schedule.index')->with('schedule_saved', 'Googleカレンダーの認証設定がまだ完了していません。');
        }

        if ($request->filled('event')) {
            $event = ScheduleEvent::query()
                ->where('user_id', $request->user()->getKey())
                ->findOrFail($request->integer('event'));
            $request->session()->put('calendar_pending_event_id', $event->getKey());
        }

        $state = Str::random(48);
        $request->session()->put('google_calendar_oauth_state', $state);

        return redirect()->away($calendar->authorizationUrl($state));
    }

    public function callback(Request $request, GoogleCalendarService $calendar): RedirectResponse
    {
        $this->ensureLocalPreview();
        $this->ensureCalendarAccess($request);
        $expectedState = $request->session()->pull('google_calendar_oauth_state');
        $receivedState = $request->query('state');

        if (! is_string($expectedState) || ! is_string($receivedState) || ! hash_equals($expectedState, $receivedState)) {
            return to_route('schedule.index')->with('schedule_saved', 'Googleの認証を確認できませんでした。もう一度連携してください。');
        }

        if ($request->filled('error') || ! is_string($request->query('code'))) {
            return to_route('schedule.index')->with('schedule_saved', 'Googleカレンダーとの連携はキャンセルされました。');
        }

        /** @var User $user */
        $user = $request->user();

        try {
            $tokens = $calendar->exchangeAuthorizationCode($request->query('code'));
            $refreshToken = $tokens['refresh_token'] ?? $user->google_calendar_refresh_token;

            if (! is_string($refreshToken) || $refreshToken === '') {
                return to_route('schedule.index')->with('schedule_saved', 'Googleから更新用の認証情報を受け取れませんでした。再連携をお試しください。');
            }

            $user->forceFill(['google_calendar_refresh_token' => $refreshToken])->save();
            $pendingEventId = $request->session()->pull('calendar_pending_event_id');

            if (is_numeric($pendingEventId)) {
                $event = ScheduleEvent::query()
                    ->where('user_id', $user->getKey())
                    ->find($pendingEventId);

                if ($event) {
                    $result = $calendar->syncEvent($user, $event);
                    $event->forceFill(['google_calendar_event_id' => $result['id']])->save();

                    return to_route('schedule.index', ['month' => $event->starts_at->timezone('Asia/Tokyo')->format('Y-m')])
                        ->with('schedule_saved', 'Googleカレンダーに予定を追加しました。');
                }
            }

            return to_route('schedule.index')->with('schedule_saved', 'Googleカレンダーを連携しました。');
        } catch (Throwable) {
            $request->session()->forget('calendar_pending_event_id');

            return to_route('schedule.index')->with('schedule_saved', 'Googleカレンダーとの連携に失敗しました。認証設定を確認してください。');
        }
    }

    public function disconnect(Request $request, GoogleCalendarService $calendar): RedirectResponse
    {
        $this->ensureLocalPreview();
        $this->ensureCalendarAccess($request);
        /** @var User $user */
        $user = $request->user();

        try {
            $calendar->revoke($user);
        } finally {
            $user->forceFill(['google_calendar_refresh_token' => null])->save();
        }

        return to_route('schedule.index')->with('schedule_saved', 'Googleカレンダーとの連携を解除しました。登録済みの予定はGoogleカレンダーに残っています。');
    }

    public function storeEvent(Request $request, ScheduleEvent $event, GoogleCalendarService $calendar): RedirectResponse
    {
        $this->ensureLocalPreview();
        $this->ensureCalendarAccess($request);
        /** @var User $user */
        $user = $request->user();
        $event = ScheduleEvent::query()
            ->where('user_id', $user->getKey())
            ->findOrFail($event->getKey());

        if (! $user->google_calendar_refresh_token) {
            return to_route('google-calendar.connect', ['event' => $event->getKey()]);
        }

        try {
            $result = $calendar->syncEvent($user, $event);
            $isNewEvent = ! $event->google_calendar_event_id;
            $event->forceFill(['google_calendar_event_id' => $result['id']])->save();

            return back()->with('schedule_saved', $isNewEvent
                ? 'Googleカレンダーに予定を追加しました。'
                : 'Googleカレンダーの予定を更新しました。');
        } catch (Throwable) {
            return back()->with('calendar_failed_event_id', $event->getKey());
        }
    }

    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.google_calendar_enabled'), 404);
    }

    private function ensureCalendarAccess(Request $request): void
    {
        abort_unless($request->user()->email !== 'demo@career-ai.local', 403, '共有デモアカウントではGoogleカレンダーを連携できません。個人アカウントでログインしてください。');
    }
}
