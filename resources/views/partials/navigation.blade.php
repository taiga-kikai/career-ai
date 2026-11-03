<button id="menu-backdrop" type="button" aria-label="メニューを閉じる" class="fixed inset-0 z-30 hidden bg-[#34271e]/35 backdrop-blur-[2px] lg:hidden"></button>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-[270px] -translate-x-full flex-col border-r border-[#eadfce] bg-[#fffaf3] px-5 py-5 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0" aria-label="メインメニュー">
    <a href="{{ route('home.preview') }}" class="flex items-center gap-3 px-2">
        <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[#e8aa62] p-0.5"><img src="{{ asset('images/career-ai-meerkat-walk.png') }}" alt="" class="size-full object-contain"></span>
        <span><span class="block font-semibold tracking-tight">Career AI</span><span class="block text-[11px] text-[#80684f]">就活を、ひとつずつ前へ。</span></span>
    </a>
    <p class="mb-2 mt-9 px-3 text-[10px] font-semibold tracking-[0.18em] text-[#a28c73]">MENU</p>
    <nav class="flex flex-col gap-1" aria-label="ページ">
        @foreach ([['home.preview', '⌂', 'ホーム'], ['applications.preview', '▤', '企業・応募管理'], ['documents.index', '▧', 'ES・書類'], ['interviews.index', '◷', '面接対策'], ['research.index', '⌕', '企業研究'], ['schedule.index', '▦', 'スケジュール']] as [$routeName, $icon, $label])
            @php($isActive = request()->routeIs($routeName) || ($routeName === 'applications.preview' && request()->routeIs('applications.*')) || ($routeName === 'research.index' && request()->routeIs('research.*')))
            <a href="{{ route($routeName) }}" @if ($isActive) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ $isActive ? 'bg-[#f3e5cf] font-semibold text-[#744c37]' : 'text-[#756452] hover:bg-[#f7efe3] hover:text-[#744c37]' }}">
                <span class="grid size-[18px] place-items-center text-base" aria-hidden="true">{{ $icon }}</span>{{ $label }}
            </a>
        @endforeach
    </nav>
    <div class="mt-auto rounded-2xl bg-[#f7ecd9] p-4"><p class="text-sm font-semibold text-[#805a3b]">✦ ひとつずつで大丈夫</p><p class="mt-2 text-[11px] leading-5 text-[#80684f]">自分のペースで、今日できることから。</p></div>
</aside>
