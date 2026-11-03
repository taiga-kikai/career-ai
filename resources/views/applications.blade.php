<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>企業・応募管理 | Career AI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/career-ai-meerkat-walk.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7efe3] text-[#3c3026] antialiased">
    <div class="min-h-screen lg:flex">
        @include('partials.navigation')

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 flex h-[70px] items-center justify-between border-b border-[#eadfce] bg-[#f7efe3]/95 px-4 backdrop-blur sm:px-7 lg:px-10">
                <div class="flex items-center gap-3"><button id="menu-toggle" type="button" aria-label="メニューを開く" aria-expanded="false" class="grid size-10 place-items-center rounded-xl border border-[#eadfce] bg-[#fffaf3] text-lg lg:hidden">☰</button><span class="text-sm font-semibold text-[#80684f]">企業・応募管理</span></div>
                <div class="flex items-center gap-3">@include('partials.account-menu')</div>
            </header>

            <main class="mx-auto max-w-[1440px] px-4 pb-12 pt-6 sm:px-7 sm:pt-8 lg:px-10 lg:pt-10">
                <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                    <div><a href="{{ route('home.preview') }}" class="text-xs font-medium text-[#a66c48] hover:underline">← ホームへ戻る</a><p class="mt-5 text-[11px] font-semibold tracking-[0.16em] text-[#a66c48]">APPLICATION TRACKER</p><h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">企業・応募管理</h1><p class="mt-2 text-sm text-[#80684f]">応募先と選考状況を、ひと目で整理できます。</p></div>
                    <div class="flex flex-wrap items-center gap-3"><p class="rounded-xl border border-[#eadfce] bg-[#fffaf3] px-4 py-2.5 text-xs text-[#80684f]">この一覧は自動で保存されます</p><button id="application-add" type="button" class="inline-flex items-center gap-2 rounded-xl bg-[#31574a] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#25483d]"><span class="text-lg leading-none" aria-hidden="true">＋</span>応募先を追加</button></div>
                </div>

                @if (session('application_saved'))
                    <p role="status" class="mb-5 rounded-xl border border-[#cbd9c4] bg-[#edf3e9] px-4 py-3 text-sm font-medium text-[#4e704f]">{{ session('application_saved') }}</p>
                @endif
                @if ($errors->any())
                    <p role="alert" class="mb-5 rounded-xl border border-[#e8c6ad] bg-[#fbf0e6] px-4 py-3 text-sm font-medium text-[#98553c]">入力を確認して、もう一度保存してください。</p>
                @endif

                <section class="mb-5 grid gap-3 sm:grid-cols-3" aria-label="応募区分を選択">
                    <button type="button" data-active-category="main" aria-pressed="false" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 text-left transition hover:-translate-y-0.5 hover:shadow-sm aria-pressed:border-[#d9a17f] aria-pressed:bg-[#fbf0e6]"><p class="text-xs text-[#80684f]">本選考</p><p class="mt-1 text-2xl font-semibold text-[#98553c]">{{ count($mainApplications) }}<span class="ml-1 text-sm font-medium">社</span></p></button>
                    <button type="button" data-active-category="summer" aria-pressed="false" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 text-left transition hover:-translate-y-0.5 hover:shadow-sm aria-pressed:border-[#bdcba9] aria-pressed:bg-[#edf3e9]"><p class="text-xs text-[#80684f]">夏インターン</p><p class="mt-1 text-2xl font-semibold text-[#55724f]">{{ count($summerApplications) }}<span class="ml-1 text-sm font-medium">件</span></p></button>
                    <button type="button" data-active-category="autumn" aria-pressed="false" class="rounded-2xl border border-[#eadfce] bg-[#fffaf3] p-4 text-left transition hover:-translate-y-0.5 hover:shadow-sm aria-pressed:border-[#bdcba9] aria-pressed:bg-[#edf3e9]"><p class="text-xs text-[#80684f]">秋・冬インターン</p><p class="mt-1 text-2xl font-semibold text-[#55724f]">{{ count($autumnApplications) }}<span class="ml-1 text-sm font-medium">件</span></p></button>
                </section>

                <section class="overflow-hidden rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3]">
                    <div class="p-4 sm:p-6">
                        <div class="mb-5 flex flex-col gap-4">
                            <label class="relative block"><span class="sr-only">企業名・コース名で検索</span><span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#a28c73]" aria-hidden="true">⌕</span><input id="application-search" type="search" placeholder="企業名・コース名で検索" class="w-full rounded-xl border border-[#e8ddce] bg-white py-3 pl-10 pr-4 text-sm outline-none transition placeholder:text-[#b3a18b] focus:border-[#c88b64] focus:ring-2 focus:ring-[#c88b64]/15"></label>
                            <div class="flex flex-wrap gap-2" role="group" aria-label="選考状況で絞り込み">
                                <button type="button" data-status-filter="all" aria-pressed="true" class="rounded-full bg-[#3c3026] px-3.5 py-2 text-xs font-semibold text-white">すべて</button>
                                <button type="button" data-status-filter="in-progress" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">選考中</button>
                                <button type="button" data-status-filter="waiting" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">結果待ち</button>
                                <button type="button" data-status-filter="route" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">応募予定</button>
                                <button type="button" data-status-filter="accepted" data-main-label="内々定" data-internship-label="参加確定" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">内々定</button>
                                <button type="button" data-status-filter="completed" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">参加済み</button>
                                <button type="button" data-status-filter="rejected" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">不合格</button>
                                <button type="button" data-status-filter="withdrawn" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">辞退・キャンセル</button>
                                <button type="button" data-status-filter="other" aria-pressed="false" class="rounded-full border border-[#e8ddce] px-3.5 py-2 text-xs font-medium text-[#685442] hover:bg-[#f7efe3]">その他</button>
                            </div>
                            <p class="-mt-2 text-[11px] leading-5 text-[#9b876f]">「応募状況未更新」は、応募したあと結果や次の選考状況のメモがまだない状態です。</p>
                        </div>

                        <div class="mb-3 hidden grid-cols-[minmax(0,1.2fr)_minmax(120px,0.7fr)_minmax(0,1.2fr)] gap-4 px-4 text-[10px] font-semibold tracking-wide text-[#a28c73] sm:grid"><span>企業・コース</span><span>選考状況</span><span>メモ <span class="ml-1 font-normal">· 行を押すと編集</span></span></div>
                        <div id="application-list" class="max-h-[62vh] divide-y divide-[#eee3d4] overflow-y-auto overscroll-contain pr-1">
                            @foreach ($mainApplications as $application)
                                @include('partials.application-row', ['application' => $application, 'category' => 'main'])
                            @endforeach
                            @foreach ($summerApplications as $application)
                                @include('partials.application-row', ['application' => $application, 'category' => 'summer'])
                            @endforeach
                            @foreach ($autumnApplications as $application)
                                @include('partials.application-row', ['application' => $application, 'category' => 'autumn'])
                            @endforeach
                        </div>
                        <p id="empty-state" hidden class="rounded-2xl bg-[#f7efe3] px-4 py-10 text-center text-sm text-[#80684f]">条件に合う応募先はありません。</p>
                        <p id="result-summary" class="mt-4 text-right text-xs text-[#a28c73]" aria-live="polite">本選考 {{ count($mainApplications) }}件を表示中</p>
                    </div>
                </section>
                <p class="mt-4 text-xs leading-5 text-[#9b876f]">企業名の行を押すと、選考状況やメモを編集できます。</p>
            </main>
        </div>
    </div>

    <dialog id="application-dialog" class="w-[min(94vw,560px)] rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3] p-0 text-[#3c3026] shadow-2xl backdrop:bg-[#34271e]/45">
        <form id="application-form" method="POST" action="{{ route('applications.store') }}" data-store-url="{{ route('applications.store') }}" data-update-url="{{ url('/applications') }}" class="p-5 sm:p-7">
            @csrf
            <input id="application-method" type="hidden" name="_method" value="PATCH" disabled>
            <div class="mb-5 flex items-start justify-between gap-4"><div><p class="text-[10px] font-semibold tracking-[0.16em] text-[#a66c48]">APPLICATION</p><h2 id="dialog-title" class="mt-1 text-xl font-semibold">応募先を追加</h2><p id="dialog-description" class="mt-1 text-xs text-[#80684f]">企業情報と選考状況を入力してください。</p></div><button id="dialog-close" type="button" aria-label="閉じる" class="grid size-9 shrink-0 place-items-center rounded-full bg-[#f7efe3] text-lg text-[#80684f] hover:bg-[#f0dfc8]">×</button></div>
            <div class="space-y-5">
                <fieldset><legend class="text-xs font-semibold text-[#685442]">区分</legend><input id="application-category" type="hidden" name="category" required><div class="mt-2 flex flex-wrap gap-2" role="radiogroup" aria-label="応募区分">
                    <button id="category-main" type="button" role="radio" aria-checked="true" data-category-choice="main" class="rounded-full border border-[#ead3c3] bg-[#fbf0e6] px-4 py-2.5 text-sm font-semibold text-[#a66c48] transition hover:-translate-y-0.5 aria-checked:border-[#c76f4c] aria-checked:bg-[#c76f4c] aria-checked:text-white">本選考</button>
                    <button id="category-summer" type="button" role="radio" aria-checked="false" data-category-choice="summer" class="rounded-full border border-[#e8dcc0] bg-[#f8efd9] px-4 py-2.5 text-sm font-semibold text-[#9d7c42] transition hover:-translate-y-0.5 aria-checked:border-[#bd9250] aria-checked:bg-[#bd9250] aria-checked:text-white">夏インターン</button>
                    <button id="category-autumn" type="button" role="radio" aria-checked="false" data-category-choice="autumn" class="rounded-full border border-[#d7e2d2] bg-[#edf3e9] px-4 py-2.5 text-sm font-semibold text-[#718b6d] transition hover:-translate-y-0.5 aria-checked:border-[#527c5e] aria-checked:bg-[#527c5e] aria-checked:text-white">秋・冬インターン</button>
                </div></fieldset>
                <label class="block text-xs font-semibold text-[#685442]"><span id="company-label">企業名</span><input id="application-company" name="company" type="text" maxlength="255" required class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal outline-none focus:border-[#c88b64] focus:ring-2 focus:ring-[#c88b64]/15"></label>
                <label class="block text-xs font-semibold text-[#685442]">コース・職種（任意）<input id="application-program" name="program" type="text" maxlength="255" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal outline-none focus:border-[#c88b64] focus:ring-2 focus:ring-[#c88b64]/15"></label>
                <fieldset><legend class="text-xs font-semibold text-[#685442]">選考状況</legend><input id="application-status-group" name="status_group" type="hidden" required><input id="application-status-detail" name="status_detail" type="hidden"><input id="application-custom-status-detail" name="custom_status_detail" type="hidden"><div id="application-status-tags" class="mt-2 flex flex-wrap gap-2" role="group" aria-label="選考状況"></div><p id="application-status-current" class="mt-2 min-h-5 text-xs font-medium text-[#80684f]"></p></fieldset>
                <label class="block text-xs font-semibold text-[#685442]">詳細・メモ（任意）<textarea id="application-detail" name="detail" rows="3" maxlength="3000" class="mt-1.5 w-full resize-y rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal outline-none focus:border-[#c88b64] focus:ring-2 focus:ring-[#c88b64]/15"></textarea></label><section id="compensation-panel" hidden class="rounded-2xl border border-[#eadfce] bg-[#f8efdf] p-4"><div class="flex flex-wrap items-center justify-between gap-2"><div><h3 class="text-sm font-semibold">初任給・待遇</h3><p class="mt-1 text-[11px] text-[#80684f]">AIが公開情報を探して要点を整理します。</p></div><button id="compensation-fetch" type="button" class="rounded-xl bg-[#31574a] px-3 py-2 text-xs font-semibold text-white">AIで調べる</button></div><div id="compensation-result" class="mt-3 space-y-2 text-xs leading-5 text-[#685442]"></div><a id="ask-company-ai" href="{{ route('research.index') }}" class="mt-3 inline-flex text-xs font-semibold text-[#a66c48] hover:underline">AIに追加で聞く →</a></section>
            </div>
            <div class="mt-6 flex items-center justify-between gap-2"><button id="application-delete" type="button" hidden class="rounded-xl border border-[#e7b9a8] px-4 py-2.5 text-sm font-semibold text-[#a94736] hover:bg-[#fff0eb]">応募先を削除</button><div class="flex justify-end gap-2"><button id="dialog-cancel" type="button" class="rounded-xl border border-[#e8ddce] px-4 py-2.5 text-sm font-medium text-[#685442] hover:bg-[#f7efe3]">キャンセル</button><button type="submit" class="rounded-xl bg-[#31574a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#25483d]">保存</button></div></div>
        </form>
        <form id="application-delete-form" method="POST" action="#" class="hidden">@csrf @method('DELETE')</form>
    </dialog>

    <dialog id="status-detail-dialog" data-delete-option-url="{{ url('/applications/status-options') }}" class="w-[min(92vw,420px)] rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-0 text-[#3c3026] shadow-2xl backdrop:bg-[#34271e]/45">
        <div class="p-5 sm:p-6"><div class="mb-4 flex items-start justify-between gap-3"><div><p class="text-[10px] font-semibold tracking-[0.14em] text-[#a66c48]">STATUS DETAIL</p><h3 id="status-detail-title" class="mt-1 text-lg font-semibold">選考の種類</h3><p class="mt-1 text-xs text-[#80684f]">内容を選択してください。</p></div><button id="status-detail-close" type="button" aria-label="選考の種類を閉じる" class="grid size-8 place-items-center rounded-full bg-[#f7efe3] text-[#80684f] hover:bg-[#f0dfc8]">×</button></div><div id="status-detail-options" class="flex flex-wrap gap-2" role="group" aria-label="選考の種類"></div><label id="status-detail-other-wrap" hidden class="mt-4 block text-xs font-semibold text-[#685442]">新しい項目<input id="status-detail-other" type="text" maxlength="70" placeholder="例：書類選考" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal outline-none focus:border-[#c88b64] focus:ring-2 focus:ring-[#c88b64]/15"></label><p id="status-detail-error" hidden role="alert" class="mt-2 text-xs text-[#98553c]">項目を選ぶか、新しい項目を入力してください。</p><div class="mt-5 flex justify-end gap-2"><button id="status-detail-cancel" type="button" class="rounded-xl border border-[#e8ddce] px-4 py-2.5 text-sm font-medium text-[#685442] hover:bg-[#f7efe3]">戻る</button><button id="status-detail-add" type="button" hidden class="rounded-xl bg-[#31574a] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#25483d]">追加して選択</button></div></div>
    </dialog>

    <script>
        const menuOpen = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('menu-backdrop');
        const toggleMenu = (isOpen) => {
            sidebar.classList.toggle('-translate-x-full', !isOpen);
            backdrop.classList.toggle('hidden', !isOpen);
            menuOpen.setAttribute('aria-expanded', String(isOpen));
        };
        menuOpen.addEventListener('click', () => toggleMenu(menuOpen.getAttribute('aria-expanded') !== 'true'));
        backdrop.addEventListener('click', () => toggleMenu(false));

        const rows = [...document.querySelectorAll('[data-application-row]')];
        const categorySwitches = [...document.querySelectorAll('[data-active-category]')];
        const filters = [...document.querySelectorAll('[data-status-filter]')];
        const search = document.getElementById('application-search');
        const summary = document.getElementById('result-summary');
        const emptyState = document.getElementById('empty-state');
        const dialog = document.getElementById('application-dialog');
        const applicationForm = document.getElementById('application-form');
        const applicationDeleteForm = document.getElementById('application-delete-form');
        const applicationDeleteButton = document.getElementById('application-delete');
        const methodOverride = document.getElementById('application-method');
        const dialogTitle = document.getElementById('dialog-title');
        const dialogDescription = document.getElementById('dialog-description');
        const categoryField = document.getElementById('application-category');
        const categoryChoices = [...document.querySelectorAll('[data-category-choice]')];
        const companyField = document.getElementById('application-company');
        const companyLabel = document.getElementById('company-label');
        const programField = document.getElementById('application-program');
        const statusGroupField = document.getElementById('application-status-group');
        const statusDetailField = document.getElementById('application-status-detail');
        const customStatusDetailField = document.getElementById('application-custom-status-detail');
        const statusTags = document.getElementById('application-status-tags');
        const statusCurrent = document.getElementById('application-status-current');
        const statusDetailDialog = document.getElementById('status-detail-dialog');
        const statusDetailTitle = document.getElementById('status-detail-title');
        const statusDetailOptions = document.getElementById('status-detail-options');
        const statusDetailOtherWrap = document.getElementById('status-detail-other-wrap');
        const statusDetailOtherField = document.getElementById('status-detail-other');
        const statusDetailAdd = document.getElementById('status-detail-add');
        const statusDetailError = document.getElementById('status-detail-error');
        const detailField = document.getElementById('application-detail');
        let activeCategory = 'main';
        let activeApplicationId = null;
        const compensationPanel = document.getElementById('compensation-panel');
        const compensationResult = document.getElementById('compensation-result');
        const compensationFetch = document.getElementById('compensation-fetch');
        const askCompanyAi = document.getElementById('ask-company-ai');
        let activeFilter = 'all';

        const statusGroups = {
            main: ['応募予定', '選考中', '結果待ち', '不合格', '内々定', '辞退', 'その他'],
            summer: ['応募予定', '選考中', '結果待ち', '不合格', '参加確定', '辞退', 'その他'],
            autumn: ['応募予定', '選考中', '結果待ち', '不合格', '参加確定', '辞退', 'その他'],
        };
        const builtInStatusDetails = {
            '応募予定': ['普通応募', '選考優遇枠', '早期選考', '推薦応募'],
            '選考中': ['説明会', '面接', '適性検査', 'ES', 'GD', 'ES・適性検査'],
            '結果待ち': ['ES', '面接', '適性検査', 'GD'],
            '不合格': ['ESで', '面接で', '適性検査で', 'GDで'],
            'その他': ['参加済み', '応募状況未更新', '選考終了'],
        };
        const savedStatusOptions = @json($customStatusOptions);
        const groupsWithDetails = {
            main: ['応募予定', '選考中', '結果待ち', '不合格', 'その他'],
            summer: ['選考中', '結果待ち', '不合格', 'その他'],
            autumn: ['応募予定', '選考中', '結果待ち', '不合格', 'その他'],
        };
        const statusGroupLabel = (group) => group;
        const updateStatusSummary = () => {
            const group = statusGroupField.value;
            const subtype = statusDetailField.value;
            statusCurrent.textContent = group ? `${group}${subtype ? `（${subtype}）` : ''}` : '状況を選んでください';
        };
        const renderStatusGroups = () => {
            statusTags.replaceChildren(...statusGroups[categoryField.value].map((group) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('aria-pressed', String(group === statusGroupField.value));
                button.dataset.statusGroupChoice = group;
                button.className = 'rounded-full border border-[#e8ddce] bg-[#f7efe3] px-3.5 py-2 text-xs font-semibold text-[#80684f] transition hover:-translate-y-0.5 aria-pressed:border-[#31574a] aria-pressed:bg-[#31574a] aria-pressed:text-white';
                button.textContent = group === '内々定' || group === '参加確定' ? `${group} ✓` : group;
                return button;
            }));
            updateStatusSummary();
        };
        const selectStatusGroup = (group, subtype = '', customSubtype = '', openPicker = true) => {
            const previousGroup = statusGroupField.value;
            statusGroupField.value = group;
            statusDetailField.value = previousGroup === group ? (subtype || statusDetailField.value) : subtype;
            customStatusDetailField.value = previousGroup === group ? customSubtype : '';
            renderStatusGroups();
            if (openPicker && groupsWithDetails[categoryField.value].includes(group)) {
                openStatusDetailPicker(group, statusDetailField.value);
            }
        };
        const openStatusDetailPicker = (group, selectedSubtype = '') => {
            statusDetailTitle.textContent = `${statusGroupLabel(group)}の内容`;
            statusDetailError.hidden = true;
            statusDetailOtherWrap.hidden = true;
            statusDetailAdd.hidden = true;
            statusDetailOtherField.value = '';
            const defaults = builtInStatusDetails[group] ?? [];
            const saved = savedStatusOptions[categoryField.value]?.[group] ?? [];
            const optionLabels = new Set(defaults);
            const options = [
                ...defaults.map((label) => ({ label, id: null })),
                ...saved.filter((option) => !optionLabels.has(option.label)),
            ];
            const listedLabels = new Set(options.map((option) => option.label));
            const unlistedCurrent = selectedSubtype && !listedLabels.has(selectedSubtype);
            const wrappers = options.map((option) => {
                const wrapper = document.createElement('span');
                wrapper.dataset.statusOptionWrapper = '';
                wrapper.className = 'inline-flex items-center overflow-hidden rounded-full border border-[#e8ddce] bg-[#f7efe3]';
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.detailChoice = option.label;
                button.setAttribute('aria-pressed', String(option.label === selectedSubtype));
                button.className = 'rounded-full px-3.5 py-2 text-xs font-semibold text-[#80684f] transition hover:-translate-y-0.5 aria-pressed:bg-[#31574a] aria-pressed:text-white';
                button.textContent = option.label;
                wrapper.append(button);
                if (option.id) {
                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.dataset.removeStatusOption = String(option.id);
                    removeButton.dataset.optionLabel = option.label;
                    removeButton.setAttribute('aria-label', `「${option.label}」を候補から削除`);
                    removeButton.className = 'grid size-7 place-items-center border-l border-[#e8ddce] text-sm text-[#a28c73] transition hover:bg-[#f1e3d2] hover:text-[#98553c]';
                    removeButton.textContent = '×';
                    wrapper.append(removeButton);
                }
                return wrapper;
            });
            statusDetailOptions.replaceChildren(...wrappers);
            const otherButton = document.createElement('button');
            otherButton.type = 'button';
            otherButton.dataset.detailChoice = '__other__';
            otherButton.setAttribute('aria-pressed', 'false');
            otherButton.className = 'rounded-full border border-[#e8ddce] bg-[#f7efe3] px-3.5 py-2 text-xs font-semibold text-[#80684f] transition hover:-translate-y-0.5 aria-pressed:border-[#31574a] aria-pressed:bg-[#31574a] aria-pressed:text-white';
            otherButton.textContent = 'その他';
            statusDetailOptions.append(otherButton);
            if (unlistedCurrent) {
                statusDetailOtherWrap.hidden = false;
                statusDetailAdd.hidden = false;
                statusDetailOtherField.value = selectedSubtype;
                otherButton.setAttribute('aria-pressed', 'true');
            }
            statusDetailDialog.showModal();
        };
        const renderCategoryTags = () => {
            categoryChoices.forEach((choice) => {
                choice.setAttribute('aria-checked', String(choice.dataset.categoryChoice === categoryField.value));
            });
        };
        categoryChoices.forEach((choice) => choice.addEventListener('click', () => {
            const previousGroup = statusGroupField.value;
            const previousSubtype = statusDetailField.value;
            categoryField.value = choice.dataset.categoryChoice;
            if (previousGroup === '参加確定' && categoryField.value === 'main') {
                statusGroupField.value = '内々定';
                statusDetailField.value = '';
                customStatusDetailField.value = '';
            } else if (previousGroup === '内々定' && categoryField.value !== 'main') {
                statusGroupField.value = '参加確定';
                statusDetailField.value = '';
                customStatusDetailField.value = '';
            } else if (previousGroup && !statusGroups[categoryField.value].includes(previousGroup)) {
                statusGroupField.value = '';
                statusDetailField.value = '';
                customStatusDetailField.value = '';
            } else {
                statusGroupField.value = previousGroup;
                const groupSupportsDetails = groupsWithDetails[categoryField.value].includes(previousGroup);
                statusDetailField.value = groupSupportsDetails ? previousSubtype : '';
                if (!groupSupportsDetails) {
                    customStatusDetailField.value = '';
                }
            }
            renderCategoryTags();
            renderStatusGroups();
        }));
        statusTags.addEventListener('click', (event) => {
            const choice = event.target.closest('[data-status-group-choice]');
            if (!choice) {
                return;
            }
            const group = choice.dataset.statusGroupChoice;
            selectStatusGroup(group, '', '', groupsWithDetails[categoryField.value].includes(group));
        });
        statusDetailOptions.addEventListener('click', (event) => {
            const option = event.target.closest('[data-detail-choice]');
            if (!option) {
                return;
            }
            if (option.dataset.detailChoice === '__other__') {
                statusDetailOtherWrap.hidden = false;
                statusDetailAdd.hidden = false;
                statusDetailOtherField.value = '';
                statusDetailError.hidden = true;
                statusDetailOtherField.focus();
                return;
            }
            statusDetailField.value = option.dataset.detailChoice;
            customStatusDetailField.value = '';
            updateStatusSummary();
            statusDetailDialog.close();
        });
        statusDetailOptions.addEventListener('click', async (event) => {
            const removeButton = event.target.closest('[data-remove-status-option]');
            if (!removeButton) {
                return;
            }
            const optionLabel = removeButton.dataset.optionLabel;
            const confirmed = window.confirm(`「${optionLabel}」を候補から削除しますか？既に企業に保存した選考状況は変わりません。`);
            if (!confirmed) {
                return;
            }
            removeButton.disabled = true;
            try {
                const response = await fetch(`${statusDetailDialog.dataset.deleteOptionUrl}/${removeButton.dataset.removeStatusOption}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ category: categoryField.value, status_group: statusGroupField.value }),
                });
                if (!response.ok) {
                    throw new Error('候補を削除できませんでした。');
                }
                const savedOptions = savedStatusOptions[categoryField.value]?.[statusGroupField.value] ?? [];
                savedStatusOptions[categoryField.value] ??= {};
                savedStatusOptions[categoryField.value][statusGroupField.value] = savedOptions.filter((option) => option.id !== Number(removeButton.dataset.removeStatusOption));
                removeButton.closest('[data-status-option-wrapper]').remove();
            } catch {
                removeButton.disabled = false;
                window.alert('候補を削除できませんでした。時間をおいてもう一度試してください。');
            }
        });
        statusDetailAdd.addEventListener('click', () => {
            const customSubtype = statusDetailOtherField.value.trim();
            if (!customSubtype) {
                statusDetailError.hidden = false;
                statusDetailOtherField.focus();
                return;
            }
            statusDetailField.value = customSubtype;
            customStatusDetailField.value = customSubtype;
            updateStatusSummary();
            statusDetailDialog.close();
        });
        document.getElementById('status-detail-close').addEventListener('click', () => statusDetailDialog.close());
        document.getElementById('status-detail-cancel').addEventListener('click', () => statusDetailDialog.close());
        applicationForm.addEventListener('submit', (event) => {
            const group = statusGroupField.value;
            const needsSubtype = group && groupsWithDetails[categoryField.value].includes(group);
            if (!group || (needsSubtype && !statusDetailField.value.trim())) {
                event.preventDefault();
                if (group) {
                    openStatusDetailPicker(group, statusDetailField.value);
                } else {
                    statusCurrent.textContent = '選考状況を選んでください。';
                }
            }
        });

        const updateList = () => {
            const query = search.value.trim().toLocaleLowerCase('ja');
            let count = 0;
            rows.forEach((row) => {
                const matches = row.dataset.category === activeCategory
                    && (activeFilter === 'all' || row.dataset.filter === activeFilter)
                    && row.dataset.search.includes(query);
                row.hidden = !matches;
                row.classList.toggle('hidden', !matches);
                if (matches) count += 1;
            });
            emptyState.hidden = count !== 0;
            const categoryNames = { main: '本選考', summer: '夏インターン', autumn: '秋・冬インターン' };
            summary.textContent = `${categoryNames[activeCategory]} ${count}件を表示中`;
        };

        const setActiveCategory = (category) => {
            activeCategory = category;
            categorySwitches.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.activeCategory === category)));
            const acceptedFilter = document.querySelector('[data-status-filter="accepted"]');
            acceptedFilter.textContent = category === 'main' ? acceptedFilter.dataset.mainLabel : acceptedFilter.dataset.internshipLabel;
            document.getElementById('application-list').scrollTop = 0;
            updateList();
        };

        const openAddDialog = () => {
            applicationForm.reset();
            activeApplicationId = null;
            compensationPanel.hidden = true;
            compensationResult.replaceChildren();
            applicationForm.action = applicationForm.dataset.storeUrl;
            methodOverride.disabled = true;
            applicationDeleteButton.hidden = true;
            categoryField.value = activeCategory;
            renderCategoryTags();
            statusGroupField.value = '';
            statusDetailField.value = '';
            customStatusDetailField.value = '';
            renderStatusGroups();
            dialogTitle.textContent = '応募先を追加';
            dialogDescription.textContent = '企業名と現在の状況を入力してください。';
            companyLabel.textContent = '企業名';
            dialog.showModal();
            companyField.focus();
        };

        const openEditDialog = (row) => {
            activeApplicationId = row.dataset.id;
            compensationPanel.hidden = false;
            renderCompensationSummary(row.dataset.compensationSummary);
            const researchUrl = new URL("{{ route('research.index') }}", window.location.href);
            researchUrl.searchParams.set('company', row.dataset.company);
            researchUrl.searchParams.set('category', row.dataset.category);
            askCompanyAi.href = researchUrl.toString();
            applicationForm.action = `${applicationForm.dataset.updateUrl}/${row.dataset.id}`;
            methodOverride.disabled = false;
            applicationDeleteForm.action = `${applicationForm.dataset.updateUrl}/${row.dataset.id}`;
            applicationDeleteButton.hidden = false;
            categoryField.value = row.dataset.category;
            renderCategoryTags();
            statusGroupField.value = row.dataset.statusGroup;
            statusDetailField.value = row.dataset.statusDetail || '';
            customStatusDetailField.value = '';
            renderStatusGroups();
            companyField.value = row.dataset.company;
            programField.value = row.dataset.program || '';
            detailField.value = row.dataset.detail || '';
            dialogTitle.textContent = '企業情報を編集';
            dialogDescription.textContent = `「${row.dataset.company}」の企業名・選考状況を変更できます。`;
            companyLabel.textContent = '企業名を編集';
            dialog.showModal();
        };

        const renderCompensationSummary = (serialized) => {
            compensationResult.replaceChildren();
            if (!serialized || serialized === 'null') return;
            let result;
            try { result = JSON.parse(serialized); } catch { return; }
            const labels = { initial_salary: '初任給', salary_structure: '給与内訳', bonus_and_raise: '賞与・昇給', benefits: '福利厚生', holidays: '休日', work_location: '勤務地', work_hours: '勤務時間', notes: '補足' };
            Object.entries(result.summary || {}).forEach(([key, value]) => {
                const item = document.createElement('p');
                item.className = 'rounded-xl bg-white/80 px-3 py-2';
                const strong = document.createElement('strong');
                strong.textContent = (labels[key] || key) + '：';
                item.append(strong, document.createTextNode(value));
                compensationResult.append(item);
            });
            (result.sources || []).forEach((source) => {
                if (!/^https?:\/\//.test(source.url || '')) return;
                const link = document.createElement('a');
                link.href = source.url; link.target = '_blank'; link.rel = 'noopener noreferrer';
                link.className = 'block text-[#a66c48] underline'; link.textContent = source.title || source.url;
                compensationResult.append(link);
            });
        };
        compensationFetch.addEventListener('click', async () => {
            const row = rows.find((item) => item.dataset.id === activeApplicationId);
            if (!row) return;
            compensationFetch.disabled = true; compensationFetch.textContent = '調査中…';
            try {
                const response = await fetch(row.dataset.summaryUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || '調査できませんでした。');
                row.dataset.compensationSummary = JSON.stringify({ summary: data.summary, sources: data.sources });
                renderCompensationSummary(row.dataset.compensationSummary);
            } catch (error) {
                compensationResult.textContent = error.message || '調査に失敗しました。';
            } finally { compensationFetch.disabled = false; compensationFetch.textContent = 'もう一度調べる'; }
        });
        document.getElementById('application-add').addEventListener('click', openAddDialog);
        document.getElementById('dialog-close').addEventListener('click', () => dialog.close());
        document.getElementById('dialog-cancel').addEventListener('click', () => dialog.close());
        applicationDeleteButton.addEventListener('click', () => {
            const company = companyField.value.trim();
            if (window.confirm(`「${company}」と、紐づくES・書類・面接準備・企業研究を削除しますか？予定は残りますが企業との関連は外れます。この操作は取り消せません。`)) {
                applicationDeleteForm.requestSubmit();
            }
        });
        rows.forEach((row) => {
            row.addEventListener('click', () => openEditDialog(row));
            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openEditDialog(row);
                }
            });
        });

        categorySwitches.forEach((button) => button.addEventListener('click', () => setActiveCategory(button.dataset.activeCategory)));

        filters.forEach((filter) => filter.addEventListener('click', () => {
            activeFilter = filter.dataset.statusFilter;
            filters.forEach((item) => {
                const selected = item === filter;
                item.setAttribute('aria-pressed', String(selected));
                item.classList.toggle('bg-[#3c3026]', selected);
                item.classList.toggle('text-white', selected);
                item.classList.toggle('font-semibold', selected);
                item.classList.toggle('border', !selected);
            });
            updateList();
        }));
        search.addEventListener('input', updateList);
        renderCategoryTags();
        renderStatusGroups();
        const initialCategory = @json($activeCategory);
        setActiveCategory(initialCategory);
        const prefill = @json($prefill);
        if (prefill.open) {
            openAddDialog();
            categoryField.value = prefill.category;
            renderCategoryTags();
            companyField.value = prefill.company;
            programField.value = prefill.program;
            renderStatusGroups();
        }
    </script>
</body>
</html>
