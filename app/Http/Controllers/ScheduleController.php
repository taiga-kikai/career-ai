<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\ScheduleEvent;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** 月間カレンダー、予定の登録・編集、完了状態、Google用の手動登録リンクを扱う。 */
class ScheduleController extends Controller
{
    private const EVENT_TYPES = ['briefing', 'interview_1', 'interview_2', 'interview_3', 'interview_final', 'ob_visit', 'internship', 'group_discussion', 'es_deadline', 'aptitude_deadline', 'other'];

    /** 指定月のカレンダー枠と、その月に開始する予定を画面へ渡す。 */
    public function index(Request $request): View
    {
        $this->ensureLocalPreview();
        $monthValue = $request->query('month');
        $monthValue = is_string($monthValue) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $monthValue) === 1 ? $monthValue : now('Asia/Tokyo')->format('Y-m');
        $monthStart = CarbonImmutable::createFromFormat('!Y-m', $monthValue, 'Asia/Tokyo');
        $gridStart = $monthStart->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthStart->endOfMonth()->endOfWeek(Carbon::MONDAY);
        $days = collect(range(0, (int) $gridStart->diffInDays($gridEnd)))->map(fn (int $offset): CarbonImmutable => $gridStart->addDays($offset));
        $events = ScheduleEvent::query()->with('application')->whereBetween('starts_at', [$monthStart->startOfMonth(), $monthStart->endOfMonth()])->orderBy('starts_at')->get();

        return view('schedule.index', [
            'month' => $monthStart,
            'previousMonth' => $monthStart->subMonth()->format('Y-m'),
            'nextMonth' => $monthStart->addMonth()->format('Y-m'),
            'days' => $days,
            'events' => $events,
            'eventsByDay' => $events->groupBy(fn (ScheduleEvent $event): string => $event->starts_at->timezone('Asia/Tokyo')->format('Y-m-d')),
        ]);
    }

    /** 日付指定があれば引き継ぎ、開始時刻と終了時刻の初期値を入れたフォームを表示する。 */
    public function create(Request $request): View
    {
        $this->ensureLocalPreview();
        $date = $request->query('date');
        $date = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) === 1 ? $date : now('Asia/Tokyo')->addDay()->format('Y-m-d');

        $initialStart = Carbon::parse($date, 'Asia/Tokyo')->setTime(10, 0);
        $initialEnd = $initialStart->copy()->addHour();

        return view('schedule.form', [
            'event' => null,
            'applications' => CareerApplication::query()->orderBy('company')->get(),
            'initialStart' => $initialStart->format('Y-m-d\TH:i'),
            'initialEnd' => $initialEnd->format('Y-m-d\TH:i'),
        ]);
    }

    /** 検証・変換した新しい予定をDBに保存する。 */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureLocalPreview();
        $event = ScheduleEvent::query()->create($this->eventData($this->validateEvent($request)));

        return to_route('schedule.index', ['month' => $event->starts_at->timezone('Asia/Tokyo')->format('Y-m')])->with('schedule_saved', '予定を追加しました。');
    }

    /** 対象予定と応募先を読み込み、編集フォームを表示する。 */
    public function edit(ScheduleEvent $event): View
    {
        $this->ensureLocalPreview();
        $event->load('application');

        return view('schedule.form', ['event' => $event, 'applications' => CareerApplication::query()->orderBy('company')->get(), 'initialStart' => $event->starts_at->timezone('Asia/Tokyo')->format('Y-m-d\TH:i')]);
    }

    /** 入力を検証・変換して既存予定を更新する。 */
    public function update(Request $request, ScheduleEvent $event): RedirectResponse
    {
        $this->ensureLocalPreview();
        $event->update($this->eventData($this->validateEvent($request)));

        return to_route('schedule.index', ['month' => $event->starts_at->timezone('Asia/Tokyo')->format('Y-m')])->with('schedule_saved', '予定を更新しました。');
    }

    /** 予定を削除し、対象予定があった月のカレンダーへ戻る。 */
    public function destroy(ScheduleEvent $event): RedirectResponse
    {
        $this->ensureLocalPreview();
        $month = $event->starts_at->timezone('Asia/Tokyo')->format('Y-m');
        $event->delete();

        return to_route('schedule.index', ['month' => $month])->with('schedule_saved', '予定を削除しました。');
    }

    /** 完了状態を反転し、画面内更新または通常の画面遷移で結果を返す。 */
    public function toggleCompleted(Request $request, ScheduleEvent $event): JsonResponse|RedirectResponse
    {
        $this->ensureLocalPreview();
        $event->update(['is_completed' => ! $event->is_completed]);

        $message = $event->is_completed ? '予定を完了にしました。' : '予定を未完了に戻しました。';

        if ($request->expectsJson()) {
            return response()->json([
                'is_completed' => $event->is_completed,
                'message' => $message,
            ]);
        }

        return back()->with('schedule_saved', $message);
    }

    /** API連携が使えない場合に、内容を埋めたGoogle Calendarの登録画面を開く。 */
    public function googleCalendarFallback(ScheduleEvent $event): RedirectResponse
    {
        $this->ensureLocalPreview();
        $payload = $this->calendarPayload($event);
        $start = $event->starts_at->timezone('Asia/Tokyo');
        $deadline = in_array($event->event_type, ['es_deadline', 'aptitude_deadline'], true);
        $end = $event->ends_at?->timezone('Asia/Tokyo') ?? $start->copy()->addMinutes($deadline ? 15 : 60);

        return redirect()->away('https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $payload['summary'],
            'dates' => $start->format('Ymd\THis').'/'.$end->format('Ymd\THis'),
            'details' => $payload['description'],
            'location' => $payload['location'] ?? '',
            'stz' => 'Asia/Tokyo',
            'etz' => 'Asia/Tokyo',
        ], '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * 手動登録リンクやCalendar APIで使う予定の題名・日時・説明を組み立てる。
     *
     * @return array<string, mixed>
     */
    private function calendarPayload(ScheduleEvent $event): array
    {
        $start = $event->starts_at->timezone('Asia/Tokyo');
        $deadline = in_array($event->event_type, ['es_deadline', 'aptitude_deadline'], true);
        $end = $event->ends_at?->timezone('Asia/Tokyo') ?? $start->copy()->addMinutes($deadline ? 15 : 60);
        $type = self::eventTypeNames()[$event->event_type] ?? '予定';
        $activity = $event->title && $event->title !== $type ? $type.'（'.$event->title.'）' : $type;
        $time = $deadline ? '~'.$start->format('H:i') : $start->format('H:i').'~'.$end->format('H:i');
        $format = match ($event->meeting_format) {
            'online' => '開催形式：オンライン',
            'offline' => '開催形式：オフライン',
            default => null,
        };
        $description = collect([
            $event->selection_type === 'main' ? '本選考' : 'インターン',
            $format,
            $event->notes,
            $event->meeting_url ? '参加URL：'.$event->meeting_url : null,
        ])->filter()->implode("\n\n");
        $payload = [
            'summary' => implode('　', array_filter([$time, $activity, $event->company_name])),
            'description' => $description,
            'start' => ['dateTime' => $start->toRfc3339String(), 'timeZone' => 'Asia/Tokyo'],
            'end' => ['dateTime' => $end->toRfc3339String(), 'timeZone' => 'Asia/Tokyo'],
        ];
        if ($event->meeting_format === 'offline' && $event->location) {
            $payload['location'] = $event->location;
        }

        return $payload;
    }

    /**
     * 表示・同期で共通して使う予定種別の日本語ラベルを返す。
     *
     * @return array<string, string>
     */
    public static function eventTypeNames(): array
    {
        return ['briefing' => '説明会', 'interview_1' => '一次面接', 'interview_2' => '二次面接', 'interview_3' => '三次面接', 'interview_final' => '最終面接', 'ob_visit' => 'OB・OG訪問', 'internship' => 'インターン', 'group_discussion' => 'GD', 'es_deadline' => 'ES締切', 'aptitude_deadline' => '適性検査締切', 'other' => 'その他'];
    }

    /**
     * 予定欄を検証し、応募先IDがログイン中の利用者のデータかも確認する。
     *
     * @return array<string, mixed>
     */
    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'career_application_id' => ['nullable', 'integer', Rule::exists('career_applications', 'id')->where('user_id', $request->user()->id)],
            'selection_type' => ['required', Rule::in(['main', 'internship'])],
            'confirmation_status' => ['required', Rule::in(['planned', 'confirmed'])],
            'event_type' => ['required', Rule::in(self::EVENT_TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'meeting_format' => ['nullable', Rule::in(['online', 'offline'])],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);
    }

    /**
     * 任意項目を応募先情報と整合させ、日時を日本時間のCarbon値へ変換する。
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function eventData(array $validated): array
    {
        $application = ! empty($validated['career_application_id'])
            ? CareerApplication::query()->findOrFail($validated['career_application_id'])
            : null;
        $validated['career_application_id'] = $application?->id;
        if ($application) {
            $validated['company_name'] = $application->company;
            $validated['selection_type'] = $application->category === 'main' ? 'main' : 'internship';
        }
        if (in_array($validated['event_type'], ['es_deadline', 'aptitude_deadline'], true)) {
            $validated['ends_at'] = null;
        }
        if (($validated['meeting_format'] ?? null) === 'online') {
            $validated['location'] = null;
        }
        $validated['title'] = $validated['title'] ?: (self::eventTypeNames()[$validated['event_type']] ?? '予定');
        $validated['starts_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $validated['starts_at'], 'Asia/Tokyo');
        $validated['ends_at'] = filled($validated['ends_at'] ?? null) ? Carbon::createFromFormat('Y-m-d\TH:i', $validated['ends_at'], 'Asia/Tokyo') : null;

        return $validated;
    }

    /** ローカルまたは機能有効化済み環境でのみスケジュールを表示可能にする。 */
    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }
}
