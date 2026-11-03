@extends('documents.layout')
@section('title', '面接対策')
@section('header-title', '面接対策')
@section('content')
@php($labels = ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン'])
<div class="mb-7"><a href="{{ route('home.preview') }}" class="text-xs font-medium text-[#a66c48] hover:underline">← ホームへ戻る</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">INTERVIEW PREP</p><h1 class="mt-1 text-2xl font-semibold sm:text-3xl">面接対策</h1><p class="mt-2 text-sm text-[#80684f]">企業ごとに伝えたいことと想定問答を整理できます。</p></div>
<section class="mb-5 grid gap-3 sm:grid-cols-3" aria-label="応募区分">
    @foreach (['main', 'summer', 'autumn'] as $key)
        <a href="{{ route('interviews.index', ['category' => $key]) }}" aria-current="{{ $category === $key ? 'true' : 'false' }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 transition hover:-translate-y-0.5 hover:shadow-sm aria-[current=true]:border-[#d9a17f] aria-[current=true]:bg-[#fbf0e6]"><p class="text-xs text-[#80684f]">{{ $labels[$key] }}</p><p class="mt-1 text-2xl font-semibold {{ $key === 'main' ? 'text-[#98553c]' : 'text-[#55724f]' }}">{{ $counts[$key] ?? 0 }}<span class="ml-1 text-sm font-medium">件</span></p></a>
    @endforeach
</section>
<section class="overflow-hidden rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3]">
    <div class="border-b border-[#eee3d4] px-5 py-4 sm:px-6"><p class="text-[10px] font-semibold tracking-[0.14em] text-[#a66c48]">{{ strtoupper($category) }}</p><h2 class="mt-1 text-lg font-semibold">{{ $labels[$category] }}の企業</h2></div>
    <div class="divide-y divide-[#eee3d4]">
        @forelse ($applications as $application)
            <a href="{{ route('interviews.show', $application) }}" class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 transition hover:bg-[#fcf7ef] sm:px-6"><div><h3 class="font-semibold">{{ $application->company }}</h3><p class="mt-1 text-xs text-[#80684f]">{{ $application->program ? $application->program.' · ' : '' }}{{ $application->status }}</p></div><span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $application->interview_preparation_count ? 'bg-[#dfe9dc] text-[#4e704f]' : 'bg-[#f3e8d9] text-[#8b735c]' }}">{{ $application->interview_preparation_count ? '準備あり' : 'これから' }}　→</span></a>
        @empty
            <div class="px-6 py-14 text-center"><p class="font-semibold">この区分の応募先はまだありません</p><p class="mt-2 text-sm text-[#80684f]">企業・応募管理に応募先を追加すると、ここに表示されます。</p><a href="{{ route('applications.preview') }}" class="mt-4 inline-flex rounded-xl border border-[#eadfce] px-4 py-2.5 text-sm font-semibold">企業・応募管理へ</a></div>
        @endforelse
    </div>
</section>
@endsection
