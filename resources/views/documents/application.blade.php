@extends('documents.layout')
@section('title', $application->company.'のES・書類')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><a href="{{ route('documents.index', ['category' => $application->category]) }}" class="text-xs font-medium text-[#a66c48] hover:underline">← ES・書類一覧へ</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">COMPANY DOCUMENTS</p><h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $application->company }}</h1><p class="mt-2 text-sm text-[#80684f]">{{ $application->program ? $application->program.' ・ ' : '' }}{{ $application->status }}</p></div>
    <a href="{{ route('documents.create', ['category' => $application->category, 'application' => $application->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#31574a] px-4 py-3 text-sm font-semibold text-white hover:bg-[#25483d]"><span class="text-lg leading-none">＋</span>ES・書類を追加</a>
</div>
@forelse ($application->documents as $document)
    <article class="mb-4 rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-5 sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-[10px] font-semibold tracking-[0.14em] text-[#a66c48]">{{ $document->created_at->format('Y/m/d') }} に保存</p><h2 class="mt-1 text-xl font-semibold">{{ $document->title }}</h2><p class="mt-1 text-xs text-[#9b876f]">{{ count($document->questions ?? []) }}問</p></div><a href="{{ route('documents.edit', $document) }}" class="rounded-xl border border-[#e8ddce] px-4 py-2.5 text-sm font-semibold text-[#685442] hover:bg-[#f7efe3]">編集</a></div>
        <div class="mt-5 space-y-3">
            @foreach ($document->questions ?? [] as $index => $item)
                <section class="rounded-2xl bg-[#f8f1e7] p-4"><p class="text-xs font-semibold text-[#a66c48]">質問 {{ $index + 1 }}</p><h3 class="mt-1 whitespace-pre-wrap font-semibold leading-6">{{ $item['question'] ?? '' }}</h3><p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-[#5f5142]">{{ $item['answer'] ?? '' }}</p></section>
            @endforeach
        </div>
        <details class="mt-5"><summary class="cursor-pointer text-xs font-semibold text-[#80684f]">貼り付けた原文を表示</summary><pre class="mt-3 whitespace-pre-wrap rounded-xl bg-white p-4 text-sm leading-6 text-[#5f5142]">{{ $document->raw_text }}</pre></details>
    </article>
@empty
    <section class="rounded-[1.5rem] border border-dashed border-[#d9c7af] bg-[#fffaf3] px-6 py-14 text-center"><p class="font-semibold">まだES・書類は登録されていません</p><p class="mt-2 text-sm text-[#80684f]">企業に提出した内容を貼り付けて、ここに保存できます。</p><a href="{{ route('documents.create', ['category' => $application->category, 'application' => $application->id]) }}" class="mt-5 inline-flex rounded-xl bg-[#31574a] px-4 py-3 text-sm font-semibold text-white hover:bg-[#25483d]">最初のESを追加</a></section>
@endforelse
@endsection
