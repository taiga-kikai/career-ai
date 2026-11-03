<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title') | Career AI</title><link rel="icon" type="image/png" href="{{ asset('images/career-ai-meerkat-walk.png') }}">@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7efe3] text-[#3c3026] antialiased">
<div class="min-h-screen lg:flex">@include('partials.navigation')
<div class="min-w-0 flex-1">
<header class="sticky top-0 z-20 flex h-[70px] items-center justify-between border-b border-[#eadfce] bg-[#f7efe3]/95 px-4 backdrop-blur sm:px-7 lg:px-10">
<div class="flex items-center gap-3"><button id="menu-toggle" type="button" aria-label="メニューを開く" aria-controls="sidebar" aria-expanded="false" class="grid size-10 place-items-center rounded-xl border border-[#eadfce] bg-[#fffaf3] text-lg lg:hidden">☰</button><span class="text-sm font-semibold text-[#80684f]">@yield('header-title', 'Career AI')</span></div>
@include('partials.account-menu')
</header>
<main class="mx-auto max-w-[1440px] px-4 pb-12 pt-6 sm:px-7 sm:pt-8 lg:px-10 lg:pt-10">
@include('partials.invitation-notice')
@include('partials.demo-notice')
@foreach (['application_saved','document_saved','interview_saved','research_saved','schedule_saved'] as $messageKey)
@if (session($messageKey))<p role="status" class="mb-5 rounded-xl border border-[#cbd9c4] bg-[#edf3e9] px-4 py-3 text-sm font-medium text-[#4e704f]">{{ session($messageKey) }}</p>@endif
@endforeach
@if ($errors->any())<p role="alert" class="mb-5 rounded-xl border border-[#e8c6ad] bg-[#fbf0e6] px-4 py-3 text-sm font-medium text-[#98553c]">入力内容を確認してください。{{ $errors->first() }}</p>@endif
@yield('content')
</main></div></div>
<script>
const menuToggle=document.getElementById('menu-toggle'),sidebar=document.getElementById('sidebar'),menuBackdrop=document.getElementById('menu-backdrop');
if(menuToggle&&sidebar&&menuBackdrop){const setMenuOpen=open=>{sidebar.classList.toggle('-translate-x-full',!open);menuBackdrop.classList.toggle('hidden',!open);menuToggle.setAttribute('aria-expanded',String(open));};menuToggle.addEventListener('click',()=>setMenuOpen(menuToggle.getAttribute('aria-expanded')!=='true'));menuBackdrop.addEventListener('click',()=>setMenuOpen(false));sidebar.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>setMenuOpen(false)));}
</script>
@stack('scripts')
</body></html>
