@if (session('invitation_link'))
<div class="mb-5 rounded-2xl border border-[#cbd9c4] bg-[#edf3e9] p-4" role="status">
    <p class="text-sm font-semibold text-[#4e704f]">招待リンクを作成しました（7日間・1回のみ有効）</p>
    <div class="mt-2 flex gap-2"><input id="invitation-link" readonly value="{{ session('invitation_link') }}" class="min-w-0 flex-1 rounded-lg border border-[#d7e2d2] bg-white px-3 py-2 text-xs text-[#53627c]"><button type="button" onclick="navigator.clipboard.writeText(document.getElementById('invitation-link').value)" class="shrink-0 rounded-lg bg-[#4e704f] px-3 py-2 text-xs font-semibold text-white">コピー</button></div>
</div>
@endif
@if (session('logged_out'))<p role="status" class="mb-5 rounded-xl border border-[#cbd9c4] bg-[#edf3e9] px-4 py-3 text-sm font-medium text-[#4e704f]">{{ session('logged_out') }}</p>@endif
