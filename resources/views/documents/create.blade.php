@extends('documents.layout')
@section('title', 'ES・書類を追加')
@section('content')
<div class="mb-6"><a href="{{ route('documents.index', ['category' => $selectedCategory]) }}" class="text-xs font-medium text-[#a66c48] hover:underline">← ES・書類一覧へ</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">NEW DOCUMENT</p><h1 class="mt-1 text-2xl font-semibold">ES・書類を追加</h1><p class="mt-2 text-sm text-[#80684f]">提出した文章を貼り付けて、質問と回答に分けて保存します。</p></div>
@if ($applications->isEmpty())
    <section class="rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-8 text-center"><p class="font-semibold">先に企業・応募管理へ応募先を追加してください。</p><a href="{{ route('applications.preview') }}" class="mt-4 inline-flex rounded-xl bg-[#31574a] px-4 py-3 text-sm font-semibold text-white">企業・応募管理へ</a></section>
@else
    @include('documents.form', ['applications' => $applications])
@endif
@endsection
