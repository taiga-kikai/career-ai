@extends('documents.layout')
@section('title', '企業研究')
@section('header-title', '企業研究')
@section('content')
@php
$categoryLabels = ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン'];
$initialMessages = $activeConversation?->messages ?? [];
$companyPrefill = request()->query('company', $activeConversation?->company_name ?? '');
$categoryPrefill = in_array(request()->query('category'), ['main','summer','autumn'], true) ? request()->query('category') : ($activeConversation?->category ?? 'main');
@endphp
<div class="grid min-h-[calc(100dvh-150px)] gap-5 xl:grid-cols-[270px_minmax(0,1fr)]">
<aside class="rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-4">
<div class="flex items-center justify-between gap-2"><h1 class="font-semibold">企業研究チャット</h1><a href="{{ route('research.index') }}" class="grid size-9 place-items-center rounded-full bg-[#f7efe3] text-xl" aria-label="新しいチャット">＋</a></div>
<p class="mt-2 text-xs leading-5 text-[#80684f]">企業を指定して、いつでも質問できます。</p>
<div class="mt-4 space-y-1">
@forelse ($conversations as $conversation)
<a href="{{ route('research.index', ['conversation' => $conversation->id]) }}" class="block rounded-xl px-3 py-2.5 {{ $activeConversation?->id === $conversation->id ? 'bg-[#f3e5cf] text-[#744c37]' : 'hover:bg-[#f7efe3]' }}"><p class="truncate text-sm font-semibold">{{ $conversation->company_name }}</p><p class="mt-1 truncate text-[11px] text-[#80684f]">{{ $conversation->title }}</p></a>
@empty
<p class="rounded-xl bg-[#f7efe3] px-3 py-4 text-xs text-[#80684f]">会話はまだありません。</p>
@endforelse
</div>
</aside>
<section class="flex min-h-[70vh] min-w-0 flex-col overflow-hidden rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3]">
<div class="border-b border-[#eee3d4] p-4 sm:p-5">
<div class="flex flex-wrap items-end gap-3">
<label class="min-w-[200px] flex-1 text-xs font-semibold text-[#685442]">企業名<input id="research-company" list="research-companies" maxlength="255" value="{{ $companyPrefill }}" placeholder="例：株式会社〇〇" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-2.5 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
<datalist id="research-companies">@foreach ($applications as $application)<option value="{{ $application->company }}">{{ $application->program }}</option>@endforeach</datalist>
<label class="text-xs font-semibold text-[#685442]">区分<select id="research-category" class="mt-1.5 block rounded-xl border border-[#e8ddce] bg-white px-3.5 py-2.5 text-sm">@foreach ($categoryLabels as $key => $label)<option value="{{ $key }}" @selected($categoryPrefill === $key)>{{ $label }}</option>@endforeach</select></label>
</div>
<div class="mt-3 flex flex-wrap items-center justify-between gap-2"><p class="text-[11px] text-[#95816c]">質問はGoogle Geminiへ送信され、検索結果の出典リンクを表示します。個人情報は入力しないでください。</p><a id="add-company-link" href="{{ route('applications.preview', ['add' => 1, 'category' => $categoryPrefill, 'company' => $companyPrefill]) }}" class="rounded-full border border-[#eadfce] px-3 py-2 text-xs font-semibold text-[#744c37] hover:bg-[#f7efe3]">企業・応募管理へ追加 ＋</a></div>
</div>
<div id="chat-messages" class="flex-1 space-y-4 overflow-y-auto p-4 sm:p-6" aria-live="polite">
@if (count($initialMessages) === 0)
<div class="mx-auto mt-12 max-w-lg text-center"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-[#f3e5cf] text-2xl text-[#744c37]">⌕</span><h2 class="mt-4 text-lg font-semibold">企業について、気軽に聞いてみよう</h2><p class="mt-2 text-sm leading-6 text-[#80684f]">事業内容や強み、初任給、福利厚生、社風など。回答の出典も確認できます。</p></div>
@endif
</div>
<form id="research-form" class="border-t border-[#eee3d4] p-3 sm:p-4"><div class="rounded-2xl border border-[#e8ddce] bg-white p-2 focus-within:border-[#c88b64]"><textarea id="research-message" rows="3" maxlength="4000" placeholder="この企業の初任給や福利厚生について教えて" class="w-full resize-y border-0 bg-transparent px-2 py-1.5 text-sm outline-none focus:ring-0"></textarea><div class="flex items-center justify-between gap-2"><p id="research-error" class="text-xs text-[#98553c]" role="alert"></p><button id="research-send" class="rounded-xl bg-[#31574a] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#25483d]">送信 ↑</button></div></div></form>
</section>
</div>
@endsection
@push('scripts')
<script>
const form=document.getElementById('research-form'),input=document.getElementById('research-message'),company=document.getElementById('research-company'),category=document.getElementById('research-category'),messages=document.getElementById('chat-messages'),errorBox=document.getElementById('research-error'),send=document.getElementById('research-send'),addLink=document.getElementById('add-company-link');
let conversationId=@json($activeConversation?->id);
const csrf=document.querySelector('meta[name="csrf-token"]').content;
function addMessage(message){
 const wrap=document.createElement('article');wrap.className='max-w-3xl rounded-2xl p-4 '+(message.role==='user'?'ml-auto bg-[#f3e5cf]':'mr-auto bg-[#f7efe3]');
 const label=document.createElement('p');label.className='mb-2 text-[10px] font-semibold tracking-wide text-[#a66c48]';label.textContent=message.role==='user'?'あなた':'Career AI';
 const body=document.createElement('p');body.className='whitespace-pre-wrap text-sm leading-6';body.textContent=message.text;wrap.append(label,body);
 if(message.sources&&message.sources.length){const sourceList=document.createElement('div');sourceList.className='mt-3 border-t border-[#e8ddce] pt-2';message.sources.forEach(source=>{const a=document.createElement('a');a.href=source.url;a.target='_blank';a.rel='noopener noreferrer';a.className='mr-3 inline-block text-xs text-[#a66c48] underline';a.textContent=source.title||source.url;sourceList.append(a)});wrap.append(sourceList)}
 messages.append(wrap);messages.scrollTop=messages.scrollHeight
}
@foreach ($initialMessages as $message)
addMessage(@json($message));
@endforeach
function syncAddLink(){const url=new URL(addLink.href);url.searchParams.set('add','1');url.searchParams.set('category',category.value);url.searchParams.set('company',company.value);addLink.href=url.toString()}
company.addEventListener('input',syncAddLink);category.addEventListener('change',syncAddLink);syncAddLink();
form.addEventListener('submit',async event=>{event.preventDefault();errorBox.textContent='';const text=input.value.trim(),companyName=company.value.trim();if(!companyName){errorBox.textContent='企業名を入力してください。';company.focus();return}if(!text)return;send.disabled=true;send.textContent='調査中…';addMessage({role:'user',text});input.value='';try{const response=await fetch(@json(route('research.chat')),{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:JSON.stringify({company_name:companyName,category:category.value,message:text,conversation_id:conversationId})});const data=await response.json();if(!response.ok)throw new Error(data.message||'回答を取得できませんでした。');conversationId=data.conversation_id;addMessage({role:'assistant',text:data.answer,sources:data.sources});const url=new URL(window.location.href);url.searchParams.set('conversation',conversationId);history.replaceState({},'',url)}catch(error){errorBox.textContent=error.message||'通信に失敗しました。質問を控えてもう一度お試しください。'}finally{send.disabled=false;send.textContent='送信 ↑'}});
input.addEventListener('keydown',event=>{if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();form.requestSubmit()}});
</script>
@endpush
