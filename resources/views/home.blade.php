@extends('documents.layout')
@section('title', 'ホーム')
@section('header-title', 'ホーム')
@section('content')
@php
    $labels = ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン'];
    $colors = ['main' => 'text-[#98553c]', 'summer' => 'text-[#9d7c42]', 'autumn' => 'text-[#55724f]'];
@endphp
<div class="mb-6 grid items-stretch gap-4">
    <section class="relative min-h-40 overflow-hidden rounded-[1.75rem] border border-[#ebdcc6] bg-[#fffaf3] px-5 py-6 sm:px-8 sm:py-8">
        <div class="relative z-10 max-w-[calc(100%_-_5rem)] sm:max-w-2xl"><p class="text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">{{ now()->locale('ja')->isoFormat('M月D日（ddd）') }} <span class="mx-1">/</span> CAREER AI</p><h1 class="mt-3 text-2xl font-semibold sm:text-3xl">今日も、一歩ずつ。</h1><p class="mt-2 text-sm leading-6 text-[#80684f]">応募状況や書類、これからの予定をまとめました。</p><p class="mt-4 inline-block max-w-full rounded-2xl rounded-bl-sm border border-[#f0dfc5] bg-[#fbf1df] px-4 py-2.5 text-xs font-medium leading-5 text-[#685442] sm:text-sm">{{ $mascotMessage }}</p></div>
        <img src="{{ asset('images/career-ai-meerkat-walk.png') }}" alt="ミーアキャット" class="pointer-events-none absolute bottom-2 right-2 h-28 w-20 object-contain sm:bottom-4 sm:right-8 sm:h-40 sm:w-28">
    </section>
</div>

<section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
    @foreach (['main','summer','autumn'] as $category)
    <a href="{{ route('applications.preview', ['category' => $category]) }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 transition hover:-translate-y-0.5 hover:shadow-sm">
        <p class="text-xs text-[#80684f]">{{ $labels[$category] }}</p><p class="mt-1 text-2xl font-semibold {{ $colors[$category] }}">{{ $categoryCounts->get($category, 0) }}<span class="ml-1 text-sm font-medium">件</span></p>
    </a>
    @endforeach
    <a href="{{ route('applications.preview') }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 transition hover:-translate-y-0.5 hover:shadow-sm"><p class="text-xs text-[#80684f]">応募予定企業</p><p class="mt-1 text-2xl font-semibold text-[#31574a]">{{ $plannedApplications }}<span class="ml-1 text-sm font-medium">社</span></p></a>
    <a href="{{ route('applications.preview') }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 transition hover:-translate-y-0.5 hover:shadow-sm"><p class="text-xs text-[#80684f]">結果待ち</p><p class="mt-1 text-2xl font-semibold text-[#846c3e]">{{ $resultWaiting->count() }}<span class="ml-1 text-sm font-medium">件</span></p></a>
</section>

<section class="mb-6 grid gap-4 sm:grid-cols-3">
    <a href="{{ route('applications.preview') }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4"><p class="text-xs text-[#80684f]">応募先合計</p><p class="mt-1 text-xl font-semibold">{{ $totalApplications }}<span class="ml-1 text-sm">社・件</span></p></a>
    <a href="{{ route('documents.index') }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4"><p class="text-xs text-[#80684f]">ES・書類</p><p class="mt-1 text-xl font-semibold">{{ $pendingDocuments->count() }}<span class="ml-1 text-sm">件 登録済み</span></p><p class="mt-1 text-[11px] text-[#a66c48]">今週更新 {{ $documentsThisWeek->count() }}件</p></a>
    <a href="{{ route('research.index') }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4"><p class="text-xs text-[#80684f]">企業情報・比較</p><p class="mt-1 text-xl font-semibold">{{ $companyProfileCount }}<span class="ml-1 text-sm">社 登録済み</span></p><p class="mt-1 text-[11px] text-[#a66c48]">項目別ランキングを見る →</p></a>
</section>

<div class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,.9fr)]">
<section class="rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3] p-5 sm:p-6">
<div class="mb-4 flex items-center justify-between"><div><p class="text-[11px] font-semibold tracking-[.14em] text-[#a66c48]">UP NEXT</p><h2 class="mt-1 text-lg font-semibold">これからの予定</h2></div><a href="{{ route('schedule.index') }}" class="text-xs font-semibold text-[#a66c48]">すべて見る →</a></div>
<div class="divide-y divide-[#eee3d4]">
@forelse ($selectionEvents as $event)
<a href="{{ route('schedule.index') }}" class="flex gap-4 py-3 first:pt-0"><div class="w-14 shrink-0 text-center"><p class="text-[10px] text-[#a66c48]">{{ $event->starts_at->locale('ja')->isoFormat('ddd') }}</p><p class="text-lg font-semibold">{{ $event->starts_at->format('m/d') }}</p></div><div class="min-w-0 flex-1 border-l-2 {{ $event->confirmation_status === 'confirmed' ? 'border-[#d7784e]' : 'border-[#98938d]' }} pl-4"><p class="text-xs font-semibold text-[#80684f]">{{ $event->starts_at->format('H:i') }}@if ($event->ends_at)〜{{ $event->ends_at->format('H:i') }}@endif · {{ $event->title }}</p><p class="mt-1 truncate text-sm font-semibold">{{ $event->company_name ?: '企業に紐づかない予定' }}</p>@if ($event->notes)<p class="mt-1 line-clamp-1 text-xs text-[#95816c]">{{ $event->notes }}</p>@endif</div></a>
@empty
<div class="py-8 text-center"><p class="text-sm text-[#80684f]">近い予定はまだありません。</p><a href="{{ route('schedule.create') }}" class="mt-3 inline-flex rounded-xl bg-[#31574a] px-4 py-2.5 text-xs font-semibold text-white">予定を追加</a></div>
@endforelse
</div></section>

<div class="space-y-5">
<section class="rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3] p-5 sm:p-6">
<p class="text-[11px] font-semibold tracking-[.14em] text-[#a66c48]">YOUR NEXT STEP</p><h2 class="mt-1 text-lg font-semibold">次にできること</h2>
<div class="mt-4 space-y-3">
<a href="{{ route('research.index') }}" class="block rounded-2xl bg-[#f8efdf] p-4"><p class="text-sm font-semibold">登録企業を比較する</p><p class="mt-1 text-xs text-[#80684f]">自分で入力した待遇や条件を項目別に見比べられます。</p></a>
<a href="{{ route('applications.preview') }}" class="block rounded-2xl bg-[#f7efe3] p-4"><p class="text-sm font-semibold">応募状況を更新する</p><p class="mt-1 text-xs text-[#80684f]">企業情報と選考状況を管理します。</p></a>
<a href="{{ route('documents.index') }}" class="block rounded-2xl bg-[#f7efe3] p-4"><p class="text-sm font-semibold">ES・書類を整理する</p><p class="mt-1 text-xs text-[#80684f]">提出予定や記入内容を確認できます。</p></a>
</div><p class="mt-5 text-xs text-[#a28c73]">準備済みの書類 {{ $preparedDocuments }}件</p>
</section>
@include('partials.neon-usage-card')
</div>
</div>
@endsection
