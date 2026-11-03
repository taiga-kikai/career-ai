@extends('documents.layout')
@section('title', $event ? '予定を編集' : '予定を追加')
@section('header-title', 'スケジュール')
@section('content')
@php($isDeadline = in_array(old('event_type', $event?->event_type ?? ''), ['es_deadline', 'aptitude_deadline'], true))
<div class="mb-6"><a href="{{ route('schedule.index', ['month' => $event ? $event->starts_at->timezone('Asia/Tokyo')->format('Y-m') : now('Asia/Tokyo')->format('Y-m')]) }}" class="text-xs font-medium text-[#a66c48] hover:underline">← スケジュールへ戻る</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">{{ $event ? 'EDIT EVENT' : 'NEW EVENT' }}</p><h1 class="mt-1 text-2xl font-semibold">{{ $event ? '予定を編集' : '予定を追加' }}</h1></div>
@if ($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">入力内容を確認してください。{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ $event ? route('schedule.update', $event) : route('schedule.store') }}" class="rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-5 sm:p-7">
    @csrf @if ($event) @method('PUT') @endif
    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block text-sm font-semibold text-[#685442]">企業名（任意）<input name="company_name" type="text" maxlength="255" value="{{ old('company_name', $event?->company_name ?: $event?->application?->company ?? '') }}" placeholder="例：株式会社〇〇" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
        <label class="block text-sm font-semibold text-[#685442]">区分<select name="selection_type" required class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"><option value="main" @selected(old('selection_type', $event?->selection_type ?? 'main') === 'main')>本選考</option><option value="internship" @selected(old('selection_type', $event?->selection_type ?? 'main') === 'internship')>インターン</option></select></label>
    </div>
    <fieldset class="mt-6"><legend class="text-sm font-semibold text-[#685442]">予定の状態</legend><div class="mt-2 flex flex-wrap gap-3">
        <label class="cursor-pointer"><input class="peer sr-only" type="radio" name="confirmation_status" value="planned" @checked(old('confirmation_status', $event?->confirmation_status ?? 'planned') === 'planned')><span class="inline-flex rounded-full border border-[#d8d8d8] bg-[#eeeeee] px-4 py-2 text-sm font-semibold text-[#666] peer-checked:border-[#777] peer-checked:bg-[#616161] peer-checked:text-white">予定（グレー）</span></label>
        <label class="cursor-pointer"><input class="peer sr-only" type="radio" name="confirmation_status" value="confirmed" @checked(old('confirmation_status', $event?->confirmation_status ?? 'planned') === 'confirmed')><span class="inline-flex rounded-full border border-[#e7b1ac] bg-[#fde8e6] px-4 py-2 text-sm font-semibold text-[#a33] peer-checked:border-[#c5221f] peer-checked:bg-[#d50000] peer-checked:text-white">確定（赤）</span></label>
    </div></fieldset>
    <div class="mt-6 grid gap-5 sm:grid-cols-2">
        <label class="block text-sm font-semibold text-[#685442]">内容<select name="event_type" required class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]">
            @foreach (['briefing' => '説明会', 'interview_1' => '一次面接', 'interview_2' => '二次面接', 'interview_3' => '三次面接', 'interview_final' => '最終面接', 'ob_visit' => 'OB・OG訪問', 'internship' => 'インターン', 'group_discussion' => 'GD', 'es_deadline' => 'ES締切', 'aptitude_deadline' => '適性検査締切', 'other' => 'その他'] as $value => $label)<option value="{{ $value }}" @selected(old('event_type', $event?->event_type ?? 'briefing') === $value)>{{ $label }}</option>@endforeach
        </select></label>
        <label class="block text-sm font-semibold text-[#685442]">補足（オンライン／オフライン）<select name="meeting_format" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"><option value="">選択しない</option><option value="online" @selected(old('meeting_format', $event?->meeting_format ?? '') === 'online')>オンライン</option><option value="offline" @selected(old('meeting_format', $event?->meeting_format ?? '') === 'offline')>オフライン</option></select></label>
        <label class="block text-sm font-semibold text-[#685442]">その他の補足（任意）<input name="title" type="text" maxlength="255" value="{{ old('title', $event?->title ?? '') }}" placeholder="例：提出フォーム、持ち物" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
        <label class="block text-sm font-semibold text-[#685442]">{{ $isDeadline ? '締切日時' : '開始日時' }}<input name="starts_at" type="datetime-local" required value="{{ old('starts_at', $initialStart) }}" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
        <label class="block text-sm font-semibold text-[#685442]">終了日時（任意）<input name="ends_at" type="datetime-local" value="{{ old('ends_at', $event?->ends_at?->timezone('Asia/Tokyo')->format('Y-m-d\TH:i') ?? '') }}" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
        <label data-location-field class="block text-sm font-semibold text-[#685442] {{ old('meeting_format', $event?->meeting_format ?? '') === 'offline' ? '' : 'hidden' }}">場所（任意）<input name="location" type="text" maxlength="255" value="{{ old('location', $event?->location ?? '') }}" placeholder="例：会社名・会場名" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
        <label class="block text-sm font-semibold text-[#685442]">参加URL（任意）<input name="meeting_url" type="url" maxlength="2048" value="{{ old('meeting_url', $event?->meeting_url ?? '') }}" placeholder="https://..." class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
    </div>
    <label class="mt-5 block text-sm font-semibold text-[#685442]">メモ（任意）<textarea name="notes" maxlength="10000" rows="4" placeholder="持ち物、事前準備、確認したいことなど" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal leading-7 outline-none focus:border-[#c88b64]">{{ old('notes', $event?->notes ?? '') }}</textarea></label>
    <div class="mt-7 flex flex-wrap justify-between gap-3"><a href="{{ route('schedule.index', ['month' => $event ? $event->starts_at->timezone('Asia/Tokyo')->format('Y-m') : now('Asia/Tokyo')->format('Y-m')]) }}" class="rounded-xl border border-[#e8ddce] px-4 py-2.5 text-sm font-semibold text-[#685442] hover:bg-[#f7efe3]">キャンセル</a><div class="flex gap-2">@if ($event)<button form="delete-schedule-event" type="submit" class="rounded-xl border border-[#e7b9a8] px-4 py-2.5 text-sm font-semibold text-[#a94736] hover:bg-[#fff0eb]">削除</button>@endif<button type="submit" class="rounded-xl bg-[#31574a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#25483d]">保存</button></div></div>
</form>
<script>
(() => {
    const format = document.querySelector('[name="meeting_format"]');
    const locationField = document.querySelector('[data-location-field]');
    const locationInput = locationField?.querySelector('input');
    format?.addEventListener('change', () => {
        const isOffline = format.value === 'offline';
        locationField?.classList.toggle('hidden', !isOffline);
        if (!isOffline && locationInput) locationInput.value = '';
    });
})();
</script>
@if ($event)<form id="delete-schedule-event" method="POST" action="{{ route('schedule.destroy', $event) }}" class="hidden">@csrf @method('DELETE')</form><script>document.getElementById('delete-schedule-event').addEventListener('submit', (event) => { if (!window.confirm('この予定を削除しますか？')) event.preventDefault(); });</script>@endif
@endsection
