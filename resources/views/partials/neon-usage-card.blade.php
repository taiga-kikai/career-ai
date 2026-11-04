@if ($neonUsage)
<section class="rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-4 sm:p-5" aria-labelledby="neon-usage-title">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-[10px] font-semibold tracking-[.14em] text-[#a66c48]">DATABASE HEALTH</p>
            <h2 id="neon-usage-title" class="mt-1 text-base font-semibold">保存容量・DB利用状況</h2>
            <p class="mt-1 text-[11px] text-[#80684f]">Neon {{ ucfirst($neonUsage['plan']) }}プラン</p>
        </div>
        <button type="button" onclick="window.location.reload()" class="rounded-lg border border-[#eadfce] px-3 py-2 text-xs font-semibold text-[#80684f] hover:bg-[#f8efdf]">更新</button>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
        <div class="rounded-xl bg-[#fbf3e7] p-3">
            <div class="flex items-baseline justify-between gap-2">
                <p class="text-xs font-semibold text-[#685442]">DB容量の目安</p>
                @if ($neonUsage['connected'] && $neonUsage['storage_used_bytes'] !== null)
                    <p class="text-xs font-semibold text-[#31574a]">
                        {{ number_format($neonUsage['storage_used_bytes'] / 1048576, 1) }} MB
                        @if ($neonUsage['storage_limit_bytes']) / {{ number_format($neonUsage['storage_limit_bytes'] / 1073741824, 1) }} GB @endif
                    </p>
                @endif
            </div>
            @if ($neonUsage['connected'] && $neonUsage['storage_limit_bytes'])
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#e8ddce]" role="progressbar" aria-label="DB保存容量" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $neonUsage['storage_percent'] }}">
                    <div class="h-full rounded-full bg-[#c4865e] transition-all" style="width: {{ $neonUsage['storage_percent'] }}%"></div>
                </div>
                <p class="mt-2 text-[11px] text-[#80684f]">残り {{ number_format($neonUsage['storage_remaining_bytes'] / 1048576, 1) }} MB</p>
            @elseif ($neonUsage['connected'])
                <p class="mt-2 text-[11px] leading-5 text-[#80684f]">使用量 {{ number_format($neonUsage['storage_used_bytes'] / 1048576, 1) }} MB · 現在のプランに固定上限はありません</p>
            @else
                <p class="mt-2 text-[11px] leading-5 text-[#80684f]">{{ $neonUsage['message'] }}</p>
            @endif
        </div>

        <div class="rounded-xl bg-[#fbf3e7] p-3">
            <div class="flex items-baseline justify-between gap-2">
                <p class="text-xs font-semibold text-[#685442]">今月のCU時間</p>
                @if ($neonUsage['compute_status'] === 'available')
                    <p class="text-xs font-semibold text-[#31574a]">
                        {{ number_format($neonUsage['compute_used_cu_hours'], 2) }}
                        @if ($neonUsage['compute_limit_cu_hours'] !== null)
                            / {{ number_format($neonUsage['compute_limit_cu_hours'], 0) }} CU時間
                        @else
                            CU時間
                        @endif
                    </p>
                @elseif ($neonUsage['compute_status'] === 'estimated')
                    <p class="text-xs font-semibold text-[#a66c48]">推定 · Free 100 CU時間/月</p>
                @endif
            </div>
            @if (in_array($neonUsage['compute_status'], ['available', 'estimated'], true) && $neonUsage['compute_limit_cu_hours'] !== null)
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#e8ddce]" role="progressbar" aria-label="今月のCU使用量" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $neonUsage['compute_percent'] ?? min(100, ($neonUsage['compute_used_cu_hours'] / max(1, $neonUsage['compute_limit_cu_hours'])) * 100) }}">
                    <div class="h-full rounded-full {{ $neonUsage['compute_status'] === 'estimated' ? 'bg-[#d9a35c]' : 'bg-[#d7784e]' }} transition-all" style="width: {{ $neonUsage['compute_percent'] ?? min(100, ($neonUsage['compute_used_cu_hours'] / max(1, $neonUsage['compute_limit_cu_hours'])) * 100) }}%"></div>
                </div>
                <p class="mt-2 text-[11px] text-[#80684f]">残り {{ number_format($neonUsage['compute_remaining_cu_hours'], 2) }} CU時間 · 0.25 CU換算で約{{ number_format($neonUsage['estimated_active_hours_remaining'], 1) }}時間</p>
                @if ($neonUsage['compute_status'] === 'estimated')
                    <p class="mt-1 text-[10px] leading-4 text-[#a28c73]">推定使用 {{ number_format($neonUsage['compute_used_cu_hours'], 2) }} CU時間 · 接続時刻と自動停止までの時間から算出</p>
                @endif
            @else
                <p class="mt-2 text-[11px] leading-5 text-[#80684f]">{{ $neonUsage['message'] }}</p>
            @endif
        </div>
    </div>

    <p class="mt-3 text-[10px] leading-4 text-[#95816c]">保存量はDB内部の論理容量の目安です。保存量を減らしてもCU時間は直接増えず、CUはDBの処理時間と大きさで消費されます。</p>
    @if ($neonUsage['compute_status'] === 'available')
        <p class="mt-1 text-[10px] text-[#a28c73]">CU実績は約15分間隔で更新 · {{ $neonUsage['updated_at']->timezone('Asia/Tokyo')->format('H:i') }}取得</p>
    @elseif ($neonUsage['compute_status'] === 'estimated')
        <p class="mt-1 text-[10px] leading-4 text-[#a28c73]">Career AI経由の接続のみを{{ number_format($neonUsage['estimated_cu_assumption'], 2) }} CU・自動停止{{ (int) config('services.neon.estimated_suspend_seconds', 300) }}秒の仮定で推定。Neon管理画面の実測値を優先してください。</p>
    @endif
    <a href="{{ $neonUsage['console_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex rounded-xl border border-[#eadfce] bg-white px-3 py-2 text-xs font-semibold text-[#80684f] hover:bg-[#f8efdf]">Neon管理画面で確認・変更 →</a>
</section>
@endif
