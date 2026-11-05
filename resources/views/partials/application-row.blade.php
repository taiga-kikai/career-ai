<div>
    <!-- Simplicity is the consequence of refined emotions. - Jean D'Alembert -->
</div>
@php
    $statusClasses = match ($application['filter']) {
        'waiting' => 'bg-[#f7efdc] text-[#846c3e]',
        'in-progress' => 'bg-[#e3e8f0] text-[#53627c]',
        'route' => 'bg-[#f4dfd5] text-[#98553c]',
        'accepted', 'completed' => 'bg-[#dfe9dc] text-[#4e704f]',
        'rejected' => 'bg-[#eee9e2] text-[#786f66]',
        'withdrawn', 'closed' => 'bg-[#eee9e2] text-[#786f66]',
        default => 'bg-[#eee9e2] text-[#685442]',
    };
    $categoryClasses = $category === 'main' ? 'bg-[#f4dfd5] text-[#98553c]' : 'bg-[#dfe9dc] text-[#4e704f]';
@endphp
<article data-application-row data-edit-application data-id="{{ $application['id'] }}" data-category="{{ $category }}" data-company="{{ $application['company'] }}" data-program="{{ $application['program'] }}" data-status="{{ $application['status'] }}" data-status-group="{{ $application['statusGroup'] }}" data-status-detail="{{ $application['statusDetail'] }}" data-detail="{{ $application['detail'] }}" data-result-date="{{ $application['resultDate'] }}" data-summary-url="{{ route('applications.compensation-summary', $application['id']) }}" data-compensation-summary='{{ json_encode($application['compensationSummary'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}' data-filter="{{ $application['filter'] }}" data-search="{{ mb_strtolower($application['company'].' '.($application['program'] ?? '').' '.$application['detail'], 'UTF-8') }}" tabindex="0" role="button" aria-label="{{ $application['company'] }}の詳細を開いて編集" class="grid cursor-pointer gap-2 px-3 py-4 text-left transition hover:bg-[#fcf7ef] focus:bg-[#fcf7ef] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#c88b64] sm:grid-cols-[minmax(0,1.2fr)_minmax(120px,0.7fr)_minmax(0,1.2fr)] sm:items-center sm:gap-4 sm:px-4">
    <div class="min-w-0">
        <div class="mb-1.5 flex flex-wrap items-center gap-2"><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $categoryClasses }}">{{ $category === 'main' ? '本選考' : 'インターン' }}</span>@if (!empty($application['program']))<span class="text-[11px] font-medium text-[#80684f]">{{ $application['program'] }}</span>@endif</div>
        <h2 class="break-words text-sm font-semibold leading-5">{{ $application['company'] }}</h2>
    </div>
    <div class="sm:py-1"><span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClasses }}">{{ $application['status'] }}</span>@if ($application['resultDate'])<span class="mt-1.5 block text-[11px] font-medium text-[#846c3e]">結果予定：{{ \Carbon\Carbon::parse($application['resultDate'])->locale('ja')->isoFormat('M月D日') }}</span>@endif</div>
    <p class="text-xs leading-5 text-[#80684f] sm:text-sm">{{ $application['detail'] }}</p>
</article>
