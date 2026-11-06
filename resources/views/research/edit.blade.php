@extends('documents.layout')
@section('title', '企業情報を編集 · '.$profile->company_name)
@section('header-title', '企業一覧・比較')
@section('content')
<div class="mb-6"><a href="{{ route('research.index', ['category' => $application->category]) }}" class="text-xs font-medium text-[#a66c48] hover:underline">← 企業一覧・比較へ戻る</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">COMPANY PROFILE</p><h1 class="mt-1 text-2xl font-semibold sm:text-3xl">{{ $profile->company_name }}</h1><p class="mt-2 text-sm text-[#80684f]">{{ $categories[$application->category] ?? $application->category }}の応募先情報を編集します。同じ企業の他の区分にも反映されます。</p></div>
<form method="POST" action="{{ route('research.profile.update', $profile) }}" class="rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-5 sm:p-7">
    @csrf @method('PUT')<input type="hidden" name="category" value="{{ $application->category }}">
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ([['initial_salary_yen','初任給','円/月','例：280000'],['average_annual_salary_yen','平均年収','円/年','例：6500000'],['average_salary_30s_yen','30代平均年収','円/年','例：6000000'],['average_overtime_hours','平均残業時間','時間/月','例：18.5'],['annual_holidays_days','年間休日','日/年','例：125'],['paid_leave_rate','有給取得率','%','例：72.5'],['housing_allowance_yen','住宅補助','円/月','例：30000']] as [$key,$label,$unit,$placeholder])
            <label class="block text-xs font-semibold text-[#685442]">{{ $label }} <span class="font-normal text-[#95816c]">({{ $unit }})</span><input name="{{ $key }}" type="number" min="0" step="{{ in_array($key, ['average_overtime_hours','paid_leave_rate'], true) ? '0.1' : '1' }}" value="{{ old($key, $profile->{$key}) }}" placeholder="{{ $placeholder }}" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal outline-none focus:border-[#c88b64]"></label>
        @endforeach
    </div>
    <label class="mt-4 block text-xs font-semibold text-[#685442]">勤務地<textarea name="work_locations" rows="2" maxlength="2000" placeholder="例：東京・大阪、転勤の有無など" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal leading-6 outline-none focus:border-[#c88b64]">{{ old('work_locations', $profile->work_locations) }}</textarea></label>
    <label class="mt-4 block text-xs font-semibold text-[#685442]">福利厚生<textarea name="benefits" rows="3" maxlength="5000" placeholder="住宅制度、休暇制度、研修など" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal leading-6 outline-none focus:border-[#c88b64]">{{ old('benefits', $profile->benefits) }}</textarea></label>
    <label class="mt-4 block text-xs font-semibold text-[#685442]">メモ<textarea name="notes" rows="3" maxlength="5000" placeholder="情報の年度や自分が気になった点など" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal leading-6 outline-none focus:border-[#c88b64]">{{ old('notes', $profile->notes) }}</textarea></label>
    <p class="mt-4 rounded-xl bg-[#f8f1e7] px-4 py-3 text-xs leading-5 text-[#80684f]">数値は入力した項目のランキングに反映されます。初任給は月額、平均年収と住宅補助は年額・月額を画面の単位に合わせて入力してください。出典や対象年度はメモに残すと比較しやすくなります。</p>
    <div class="mt-6 flex flex-wrap justify-end gap-2"><a href="{{ route('research.index', ['category' => $application->category]) }}" class="rounded-xl border border-[#e8ddce] px-4 py-2.5 text-sm font-semibold text-[#685442]">キャンセル</a><button type="submit" class="rounded-xl bg-[#31574a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#25483d]">保存してランキング更新</button></div>
</form>
@endsection
