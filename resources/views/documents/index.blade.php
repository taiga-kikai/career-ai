@extends('documents.layout')
@section('title', 'ES・書類')
@section('content')
@php($categoryLabels = ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン'])
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div><a href="{{ route('home.preview') }}" class="text-xs font-medium text-[#a66c48] hover:underline">← ホームへ戻る</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">ENTRY SHEETS</p><h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">ES・書類</h1><p class="mt-2 text-sm text-[#80684f]">応募先ごとに、提出した内容をまとめて見返せます。</p></div>
    <a href="{{ route('documents.create', ['category' => $category]) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#31574a] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#25483d]"><span class="text-lg leading-none">＋</span>ES・書類を追加</a>
</div>
<section class="mb-5 grid gap-3 sm:grid-cols-3" aria-label="応募区分">
    @foreach (['main', 'summer', 'autumn'] as $key)
        <a href="{{ route('documents.index', ['category' => $key, 'status' => $status]) }}" aria-current="{{ $category === $key ? 'true' : 'false' }}" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 text-left transition hover:-translate-y-0.5 hover:shadow-sm aria-[current=true]:border-[#d9a17f] aria-[current=true]:bg-[#fbf0e6]">
            <p class="text-xs text-[#80684f]">{{ $categoryLabels[$key] }}</p><p class="mt-1 text-2xl font-semibold {{ $key === 'main' ? 'text-[#98553c]' : 'text-[#55724f]' }}">{{ $counts[$key] }}<span class="ml-1 text-sm font-medium">{{ $key === 'main' ? '社' : '件' }}</span></p>
        </a>
    @endforeach
</section>
<section class="overflow-hidden rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3]">
    <div class="border-b border-[#eee3d4] p-4 sm:px-6 sm:pt-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><div><p class="text-[10px] font-semibold tracking-[0.14em] text-[#a66c48]">{{ strtoupper($category) }}</p><h2 class="mt-1 text-lg font-semibold">{{ $categoryLabels[$category] }}の応募先</h2></div><p class="text-xs text-[#9b876f]">企業名を押すと、保存したES・書類を確認できます。</p></div>
        <nav class="flex flex-wrap gap-2" aria-label="記入状況">
            @foreach (['all' => 'すべて', 'filled' => '記入済み', 'unfilled' => '未記入'] as $key => $label)
                <a href="{{ route('documents.index', ['category' => $category, 'status' => $key]) }}" aria-current="{{ $status === $key ? 'true' : 'false' }}" class="rounded-full border border-[#e8ddce] px-4 py-2 text-xs font-semibold text-[#685442] transition hover:bg-[#f7efe3] aria-[current=true]:border-[#31574a] aria-[current=true]:bg-[#31574a] aria-[current=true]:text-white">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
    <div class="divide-y divide-[#eee3d4]">
        @forelse ($applications as $application)
            <a href="{{ route('documents.application', $application) }}" class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 transition hover:bg-[#fcf7ef] sm:px-6">
                <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold">{{ $application->company }}</h3>@if ($application->program)<span class="text-xs text-[#80684f]">{{ $application->program }}</span>@endif</div><p class="mt-1 text-xs text-[#9b876f]">現在の状況：{{ $application->status }}</p></div>
                <div class="flex shrink-0 items-center gap-3"><span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $application->documents_count ? 'bg-[#dfe9dc] text-[#4e704f]' : 'bg-[#f3e8d9] text-[#8b735c]' }}">{{ $application->documents_count ? '記入済み' : '未記入' }}</span><span class="text-xs text-[#9b876f]">{{ $application->documents_count }}件</span><span class="text-[#a28c73]" aria-hidden="true">→</span></div>
            </a>
        @empty
            <div class="px-6 py-14 text-center"><p class="font-semibold">{{ $status === 'all' ? 'この区分の応募先はまだありません' : '該当する企業はありません' }}</p><p class="mt-2 text-sm text-[#80684f]">企業・応募管理で応募先を追加すると、ここに表示されます。</p><a href="{{ route('applications.preview') }}" class="mt-4 inline-flex rounded-xl border border-[#eadfce] px-4 py-2.5 text-sm font-semibold text-[#685442] hover:bg-[#f7efe3]">企業・応募管理へ</a></div>
        @endforelse
    </div>
</section>
@endsection
