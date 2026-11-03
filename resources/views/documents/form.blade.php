@php($isEditing = isset($document))
@php($questionRows = old('questions', $isEditing ? ($document->questions ?? []) : [['question' => '', 'answer' => '']]))
<form id="es-document-form" method="POST" action="{{ $isEditing ? route('documents.update', $document) : route('documents.store') }}" data-parse-url="{{ route('documents.parse') }}" class="rounded-[1.5rem] border border-[#eadfce] bg-[#fffaf3] p-5 sm:p-7">
    @csrf
    @if ($isEditing) @method('PUT') @endif
    @unless ($isEditing)
        <label class="mb-5 block text-sm font-semibold text-[#685442]">企業・応募先
            <select name="career_application_id" required class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]">
                <option value="">企業を選んでください</option>
                @foreach ($applications as $application)
                    <option value="{{ $application->id }}" @selected((string) old('career_application_id', $selectedApplication) === (string) $application->id)>{{ $application->company }}（{{ ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン'][$application->category] }}）</option>
                @endforeach
            </select>
        </label>
    @else
        <div class="mb-5 rounded-xl bg-[#f8f1e7] px-4 py-3 text-sm font-semibold">{{ $document->application->company }}</div>
    @endunless
    <label class="mb-5 block text-sm font-semibold text-[#685442]">書類の名前
        <input name="title" type="text" maxlength="255" required value="{{ old('title', $isEditing ? $document->title : '') }}" placeholder="例：エントリーシート（本選考）" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#c88b64]">
    </label>
    <label class="block text-sm font-semibold text-[#685442]">企業に提出したES・書類を貼り付け
        <textarea id="document-raw-text" name="raw_text" required maxlength="50000" rows="12" placeholder="設問と回答をまとめて貼り付けてください" class="mt-2 w-full rounded-xl border border-[#e8ddce] bg-white px-4 py-3 text-sm font-normal leading-7 outline-none focus:border-[#c88b64]">{{ old('raw_text', $isEditing ? $document->raw_text : '') }}</textarea>
    </label>
    <div class="mt-3 flex flex-wrap items-center gap-3">
        <button id="parse-document" type="button" class="rounded-xl bg-[#31574a] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#25483d]">AIで質問ごとに整理</button>
        <p class="text-xs leading-5 text-[#80684f]">Gemini設定時は、ボタンを押すと貼り付けた文章がGoogleに送信されます。無料枠では内容がGoogleのサービス改善に使われる場合があるため、氏名・住所などは除いてください。結果を確認・編集してから保存できます。</p>
    </div>
    <p id="parse-message" class="mt-2 min-h-5 text-xs text-[#80684f]" aria-live="polite">@if (!config('services.gemini.api_key'))Gemini未設定のため、今は見出しから簡易的に分割します。@endif</p>
    <div class="mt-7 border-t border-[#eee3d4] pt-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-semibold">質問と回答</h2><p class="mt-1 text-xs text-[#80684f]">整理結果は保存前に自由に直せます。</p></div><button id="add-question" type="button" class="rounded-xl border border-[#d6c5ae] px-3.5 py-2 text-xs font-semibold text-[#685442] hover:bg-[#f7efe3]">＋ 質問を追加</button></div>
        <div id="question-editor" class="space-y-4">
            @foreach ($questionRows as $index => $item)
                <section data-question-card class="rounded-2xl bg-[#f8f1e7] p-4 sm:p-5">
                    <div class="mb-3 flex items-center justify-between"><p class="text-xs font-semibold text-[#a66c48]">質問 {{ $index + 1 }}</p><button type="button" data-remove-question class="text-xs font-semibold text-[#a94736] hover:underline">削除</button></div>
                    <label class="block text-xs font-semibold text-[#685442]">質問<textarea data-question-field="question" name="questions[{{ $index }}][question]" required maxlength="1500" rows="2" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal leading-6">{{ $item['question'] ?? '' }}</textarea></label>
                    <label class="mt-3 block text-xs font-semibold text-[#685442]">回答<textarea data-question-field="answer" name="questions[{{ $index }}][answer]" maxlength="8000" rows="5" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal leading-6">{{ $item['answer'] ?? '' }}</textarea></label>
                </section>
            @endforeach
        </div>
    </div>
    <div class="mt-7 flex flex-wrap justify-end gap-2"><a href="{{ $isEditing ? route('documents.application', $document->application) : route('documents.index', ['category' => $selectedCategory]) }}" class="rounded-xl border border-[#e8ddce] px-4 py-2.5 text-sm font-semibold text-[#685442] hover:bg-[#f7efe3]">キャンセル</a><button type="submit" class="rounded-xl bg-[#31574a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#25483d]">保存</button></div>
</form>
@push('scripts')
<script>
    const editor = document.getElementById('question-editor');
    const message = document.getElementById('parse-message');
    const rawText = document.getElementById('document-raw-text');
    const renumberQuestions = () => {
        [...editor.querySelectorAll('[data-question-card]')].forEach((card, index) => {
            card.querySelector('p').textContent = `質問 ${index + 1}`;
            card.querySelectorAll('[data-question-field]').forEach((field) => {
                field.name = `questions[${index}][${field.dataset.questionField}]`;
            });
        });
    };
    const addQuestionCard = (question = '', answer = '') => {
        const card = document.createElement('section');
        card.dataset.questionCard = '';
        card.className = 'rounded-2xl bg-[#f8f1e7] p-4 sm:p-5';
        const index = editor.children.length;
        card.innerHTML = `<div class="mb-3 flex items-center justify-between"><p class="text-xs font-semibold text-[#a66c48]"></p><button type="button" data-remove-question class="text-xs font-semibold text-[#a94736] hover:underline">削除</button></div><label class="block text-xs font-semibold text-[#685442]">質問<textarea data-question-field="question" required maxlength="1500" rows="2" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal leading-6"></textarea></label><label class="mt-3 block text-xs font-semibold text-[#685442]">回答<textarea data-question-field="answer" maxlength="8000" rows="5" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 text-sm font-normal leading-6"></textarea></label>`;
        card.querySelector('[data-question-field="question"]').value = question;
        card.querySelector('[data-question-field="answer"]').value = answer;
        editor.append(card);
        renumberQuestions();
    };
    document.getElementById('add-question').addEventListener('click', () => addQuestionCard());
    editor.addEventListener('click', (event) => {
        if (event.target.closest('[data-remove-question]')) {
            event.target.closest('[data-question-card]').remove();
            renumberQuestions();
        }
    });
    document.getElementById('parse-document').addEventListener('click', async (event) => {
        if (!rawText.value.trim()) {
            message.textContent = '先にES・書類の文章を貼り付けてください。';
            rawText.focus();
            return;
        }
        const hasAnswers = [...editor.querySelectorAll('[data-question-field]')].some((field) => field.value.trim());
        if (hasAnswers && !window.confirm('今の質問・回答欄を整理結果で置き換えますか？')) return;
        const button = event.currentTarget;
        button.disabled = true;
        button.textContent = '整理しています…';
        message.textContent = '文章を整理しています。少し待ってね。';
        try {
            const response = await fetch(document.getElementById('es-document-form').dataset.parseUrl, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({raw_text: rawText.value}),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || '文章を整理できませんでした。');
            editor.replaceChildren();
            result.questions.forEach((item) => addQuestionCard(item.question, item.answer));
            message.textContent = result.message || (result.source === 'gemini' ? 'Geminiで整理しました。内容を確認してください。' : '簡易整理に切り替えました。区切りや内容を確認してください。');
        } catch (error) {
            message.textContent = error.message || '文章を整理できませんでした。';
        } finally {
            button.disabled = false;
            button.textContent = 'AIで質問ごとに整理';
        }
    });
</script>
@endpush
