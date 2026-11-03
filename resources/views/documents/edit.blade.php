@extends('documents.layout')
@section('title', 'ES・書類を編集')
@section('content')
<div class="mb-6"><a href="{{ route('documents.application', $document->application) }}" class="text-xs font-medium text-[#a66c48] hover:underline">← {{ $document->application->company }}へ戻る</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">EDIT DOCUMENT</p><h1 class="mt-1 text-2xl font-semibold">ES・書類を編集</h1></div>
@include('documents.form', ['document' => $document])
<form id="delete-document-form" method="POST" action="{{ route('documents.destroy', $document) }}" class="mt-4 flex justify-end">@csrf @method('DELETE')<button type="button" id="delete-document" class="rounded-xl border border-[#e7b9a8] px-4 py-2.5 text-sm font-semibold text-[#a94736] hover:bg-[#fff0eb]">このES・書類を削除</button></form>
<script>document.getElementById('delete-document').addEventListener('click', () => { if (window.confirm('このES・書類を削除しますか？この操作は取り消せません。')) document.getElementById('delete-document-form').requestSubmit(); });</script>
@endsection
