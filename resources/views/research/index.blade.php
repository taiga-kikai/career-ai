@extends('documents.layout')
@section('title', '企業一覧・比較')
@section('header-title', '企業一覧・比較')
@section('content')
@php($categoryLabels = ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン'])
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><a href="{{ route('home.preview') }}" class="text-xs font-medium text-[#a66c48] hover:underline">← ホームへ戻る</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">COMPANY DIRECTORY</p><h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">企業一覧・比較</h1><p class="mt-2 text-sm text-[#80684f]">自分で入力した企業情報を、項目ごとに比べられます。AIによる自動調査は行いません。</p></div>
    @if ($editableApplications->isNotEmpty())<a href="{{ route('research.edit', $editableApplications->first()) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#31574a] px-4 py-3 text-sm font-semibold text-white hover:bg-[#25483d]">＋ 企業情報を入力</a>@else<a href="{{ route('applications.preview') }}" class="inline-flex items-center gap-2 rounded-xl border border-[#eadfce] bg-[#fffaf3] px-4 py-3 text-sm font-semibold text-[#685442]">企業・応募管理へ →</a>@endif
</div>

<section class="mb-6 rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-semibold">登録企業</h2><p class="mt-1 text-xs text-[#80684f]">企業・応募管理に登録した自分の企業だけを選べます。</p></div><span class="rounded-full bg-[#f7efe3] px-3 py-1.5 text-xs font-semibold text-[#685442]">情報入力済み {{ $profiles->count() }}社</span></div>
    <div class="mt-4 flex flex-wrap gap-2"><a href="{{ route('research.index') }}" aria-current="{{ $activeCategory ? 'false' : 'true' }}" class="rounded-full border px-4 py-2 text-xs font-semibold aria-[current=true]:border-[#31574a] aria-[current=true]:bg-[#31574a] aria-[current=true]:text-white">すべて</a>@foreach ($categories as $key => $label)<a href="{{ route('research.index', ['category' => $key]) }}" aria-current="{{ $activeCategory === $key ? 'true' : 'false' }}" class="rounded-full border border-[#eadfce] px-4 py-2 text-xs font-semibold text-[#685442] hover:bg-[#f7efe3] aria-[current=true]:border-[#31574a] aria-[current=true]:bg-[#31574a] aria-[current=true]:text-white">{{ $label }}</a>@endforeach</div>
</section>

<section class="mb-8">
    <div class="mb-3 flex items-end justify-between gap-3"><div><p class="text-[10px] font-semibold tracking-[0.15em] text-[#a66c48]">RANKINGS</p><h2 class="mt-1 text-lg font-semibold">項目別ランキング</h2></div><p class="text-[11px] text-[#95816c]">未入力は対象外。同じ数値は同順位です。</p></div>
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($rankings as $ranking)
            <article class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 sm:p-5"><div class="flex items-baseline justify-between gap-2"><h3 class="font-semibold">{{ $ranking['label'] }}</h3><span class="text-[10px] text-[#95816c]">{{ $ranking['direction'] === 'desc' ? '高い順' : '少ない順' }}</span></div>
                @if ($ranking['companies'])<ol class="mt-3 divide-y divide-[#eee3d4]">@foreach ($ranking['companies'] as $item)<li class="flex items-center gap-3 py-2.5 first:pt-0"><span class="grid size-7 shrink-0 place-items-center rounded-full {{ $item['rank'] === 1 ? 'bg-[#f1dfba] text-[#805a3b]' : 'bg-[#f7efe3] text-[#80684f]' }} text-xs font-bold">{{ $item['rank'] }}</span><span class="min-w-0 flex-1 truncate text-sm font-semibold">{{ $item['profile']->company_name }}</span><span class="shrink-0 text-sm font-semibold text-[#31574a]">{{ str_contains($ranking['unit'], '円') ? number_format((float) $item['value']) : number_format((float) $item['value'], 1) }}<span class="ml-1 text-[10px] font-normal text-[#80684f]">{{ $ranking['unit'] }}</span></span></li>@endforeach</ol>
                @else<p class="mt-4 rounded-xl bg-[#f8f1e7] px-3 py-4 text-xs text-[#80684f]">この項目のデータはまだありません。</p>@endif
            </article>
        @endforeach
    </div>
</section>

<section class="mb-8">
    <div class="mb-3"><p class="text-[10px] font-semibold tracking-[0.15em] text-[#a66c48]">COMPANY DATA</p><h2 class="mt-1 text-lg font-semibold">情報を入力した企業</h2></div>
    @if ($profiles->isEmpty())<div class="rounded-2xl border border-dashed border-[#d8c8b2] bg-[#fffaf3] px-5 py-10 text-center"><p class="font-semibold">企業情報はまだ登録されていません</p><p class="mt-2 text-sm text-[#80684f]">初任給や休日など、分かる項目だけ入力するとランキングに表示されます。</p>@if ($editableApplications->isNotEmpty())<a href="{{ route('research.edit', $editableApplications->first()) }}" class="mt-4 inline-flex rounded-xl bg-[#31574a] px-4 py-2.5 text-sm font-semibold text-white">最初の企業情報を入力</a>@else<a href="{{ route('applications.preview') }}" class="mt-4 inline-flex rounded-xl border border-[#eadfce] px-4 py-2.5 text-sm font-semibold text-[#685442]">先に企業を登録</a>@endif</div>
    @else<div class="grid gap-3 md:grid-cols-2">@foreach ($profiles as $profile)
        @php($application = $profile->applications->first())
        <article class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 sm:p-5"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><h3 class="truncate font-semibold">{{ $profile->company_name }}</h3><div class="mt-2 flex flex-wrap gap-1.5">@foreach ($profile->applications->pluck('category')->unique() as $profileCategory)<span class="rounded-full bg-[#f7efe3] px-2.5 py-1 text-[10px] font-medium text-[#80684f]">{{ $categoryLabels[$profileCategory] ?? $profileCategory }}</span>@endforeach</div></div>@if ($application)<a href="{{ route('research.edit', $application) }}" class="shrink-0 rounded-lg border border-[#eadfce] px-3 py-2 text-xs font-semibold text-[#685442] hover:bg-[#f7efe3]">編集</a>@endif</div>
        <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-xs">@foreach ([['initial_salary_yen','初任給','円/月'],['average_annual_salary_yen','平均年収','円'],['average_salary_30s_yen','30代平均年収','円'],['average_overtime_hours','平均残業','時間/月'],['annual_holidays_days','年間休日','日'],['paid_leave_rate','有給取得率','%'],['housing_allowance_yen','住宅補助','円/月']] as [$key,$label,$unit])@if ($profile->{$key} !== null)<div><dt class="text-[#95816c]">{{ $label }}</dt><dd class="mt-1 font-semibold">{{ str_contains($unit, '円') ? number_format((float) $profile->{$key}) : number_format((float) $profile->{$key}, 1) }} {{ $unit }}</dd></div>@endif @endforeach</dl>
        @if ($profile->work_locations)<p class="mt-3 text-xs leading-5 text-[#685442]"><span class="font-semibold">勤務地：</span>{{ $profile->work_locations }}</p>@endif @if ($profile->benefits)<p class="mt-2 whitespace-pre-line text-xs leading-5 text-[#685442]"><span class="font-semibold">福利厚生：</span>{{ $profile->benefits }}</p>@endif @if ($profile->notes)<p class="mt-2 whitespace-pre-line text-xs leading-5 text-[#80684f]">{{ $profile->notes }}</p>@endif
        </article>
    @endforeach</div>@endif
</section>

@if ($editableApplications->isNotEmpty())<section class="rounded-2xl border border-[#eadfce] bg-[#f8f1e7] p-4 sm:p-5"><h2 class="font-semibold">情報を追加できる企業</h2><p class="mt-1 text-xs text-[#80684f]">企業・応募管理に登録済みで、まだ比較情報がない企業です。</p><div class="mt-3 flex flex-wrap gap-2">@foreach ($editableApplications as $application)<a href="{{ route('research.edit', $application) }}" class="rounded-full border border-[#e3d4c1] bg-[#fffaf3] px-3 py-2 text-xs font-medium text-[#685442] hover:border-[#c88b64]">＋ {{ $application->company }}（{{ $categoryLabels[$application->category] ?? $application->category }}）</a>@endforeach</div></section>@endif
@endsection
