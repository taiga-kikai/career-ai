<details class="relative">
    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-[#eadfce] bg-[#fffaf3] p-1.5 pr-3 text-left hover:bg-white">
        <span class="grid size-8 place-items-center rounded-full bg-[#e8aa62] text-xs font-semibold text-[#573a2e]">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
        <span class="hidden max-w-36 truncate text-xs font-medium text-[#685442] sm:block">{{ auth()->user()->name }}</span>
        <span aria-hidden="true" class="text-[10px] text-[#80684f]">⌄</span>
    </summary>
    <div class="absolute right-0 top-12 z-40 w-64 rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-3 shadow-xl">
        <p class="truncate px-2 py-1 text-xs font-semibold">{{ auth()->user()->email }}</p>
        @if (app()->environment('local') || auth()->user()->email !== 'demo@career-ai.local')
            <form method="POST" action="{{ route('invitations.create') }}" class="mt-2">@csrf<button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">招待リンクを作成</button></form>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-[#eee3d4] pt-2">@csrf<button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-xs font-medium text-[#98553c] hover:bg-[#fbf0e6]">ログアウト</button></form>
    </div>
</details>
