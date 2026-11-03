@if (! app()->environment('local') && auth()->user()->email === 'demo@career-ai.local')
    <aside class="mb-5 rounded-2xl border border-[#ead7b9] bg-[#fff3da] px-4 py-3 text-sm leading-6 text-[#735532]" role="note">
        これは公開デモです。入力内容はほかのデモ利用者にも見える場合があり、保存データは予告なく消えることがあります。個人情報や実際の就活情報は入力しないでください。
    </aside>
@endif
