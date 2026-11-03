<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>ログイン | Career AI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/career-ai-meerkat-walk.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes meerkat-journey {
            0% { left: 8%; opacity: 0; }
            7% { opacity: 1; }
            92% { left: 70%; opacity: 1; }
            100% { left: 70%; opacity: 0; }
        }
        @keyframes meerkat-bob {
            from { transform: translateY(0); }
            to { transform: translateY(-3px); }
        }
        @keyframes ground-drift {
            to { transform: translateX(-50%); }
        }
        .meerkat-journey { animation: meerkat-journey 13s linear infinite; }
        .meerkat-sprite { animation: meerkat-bob .38s ease-in-out infinite alternate; }
        .ground-scroll { animation: ground-drift 4s linear infinite; }
        @media (prefers-reduced-motion: reduce) {
            .meerkat-journey { animation: none; left: 43%; opacity: 1; }
            .meerkat-sprite, .ground-scroll { animation: none; }
        }
        @media (max-height: 700px) {
            .login-main { padding-top: .5rem; padding-bottom: .5rem; }
            .login-scene { height: 5.25rem; }
            .login-heading { margin-bottom: .75rem; }
            .login-form { gap: .65rem; }
            .login-input { padding-top: .65rem; padding-bottom: .65rem; }
            .invite-note { margin-top: .8rem; margin-bottom: .8rem; }
            .mobile-tagline { display: none; }
        }
    </style>
</head>
<body class="h-dvh overflow-hidden bg-[#f7efe3] text-[#3c3026] antialiased">
    <main class="grid h-full min-h-0 lg:grid-cols-[minmax(0,1.5fr)_minmax(390px,0.85fr)]">
        <section class="relative hidden h-full min-h-0 overflow-hidden px-12 py-8 lg:flex lg:flex-col xl:px-20 xl:py-10">
            <div class="absolute inset-0 bg-[#f7efe3]"></div>
            <div class="relative z-10 flex items-center gap-3">
                <div class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[#e8aa62] p-0.5">
                    <img src="{{ asset('images/career-ai-meerkat-walk.png') }}" alt="" class="size-full object-contain">
                </div>
                <div>
                    <p class="text-lg font-semibold tracking-tight">Career AI</p>
                    <p class="text-xs text-[#725c48]">就活を、ひとつずつ前へ。</p>
                </div>
            </div>
            <div class="relative z-10 my-auto max-w-xl py-8">
                <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-[#bd895b]/30 bg-white/55 px-3 py-1.5 text-xs font-semibold tracking-wide text-[#9c593c]">
                    <span class="size-1.5 rounded-full bg-[#d7784e]"></span>
                    あなたの就活パートナー
                </p>
                <h1 class="max-w-[12ch] text-4xl font-bold leading-[1.35] tracking-tight text-[#47362b] xl:text-5xl">やることも、<br>考えたことも。<br><span class="text-[#bc6845]">ここにまとめよう。</span></h1>
                <p class="mt-5 max-w-lg text-base leading-8 text-[#655444] xl:text-lg">企業選びから応募、ESや面接の準備まで。就活の進み具合を整理して、次の一歩を見つけやすくします。</p>
                <div class="mt-5 flex max-w-xl items-center gap-4">
                    <p class="flex w-40 shrink-0 items-center gap-2 text-sm font-medium leading-6 text-[#66513e]">
                        <span class="grid size-7 shrink-0 place-items-center rounded-full bg-[#e8aa62] text-[#573a2e]" aria-hidden="true">✦</span>
                        <span>今の自分から、<br>次の一歩へ。</span>
                    </p>
                    <div class="relative h-24 min-w-0 flex-1 overflow-hidden" aria-hidden="true">
                        <svg class="absolute right-2 top-0 size-12" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" stroke="#80583d" stroke-linecap="round" stroke-width="3.5"><circle cx="32" cy="32" r="15" fill="#f2c875"/><path d="M32 6v7 M32 51v7 M6 32h7 M51 32h7 M13.5 13.5l5 5 M45.5 45.5l5 5 M50.5 13.5l-5 5 M18.5 45.5l-5 5"/></g>
                        </svg>
                        <svg class="ground-scroll absolute bottom-2 left-0 h-7 w-[200%]" viewBox="0 0 640 32" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 17 Q28 13 54 17 T108 17 Q137 21 162 16 T216 17 Q243 13 270 17 T320 17 M320 17 Q348 13 374 17 T428 17 Q457 21 482 16 T536 17 Q563 13 590 17 T640 17" fill="none" stroke="#80583d" stroke-linecap="round" stroke-width="3"/>
                        </svg>
                        <div class="meerkat-journey absolute bottom-2 left-[8%] h-[76px] w-[72px]">
                            <img src="{{ asset('images/career-ai-meerkat-walk.png') }}" alt="" class="meerkat-sprite h-full w-full object-contain object-bottom" />
                        </div>
                    </div>
                </div>
            </div>
            <p class="relative z-10 text-xs text-[#735f4d]">自分のペースで、納得できる就活を。</p>
        </section>

        <section class="relative flex h-full min-h-0 flex-col px-5 py-3 sm:px-10 sm:py-5 lg:px-8 xl:px-12">
            <div class="relative z-10 flex shrink-0 items-center gap-3 lg:hidden">
                <div class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-xl bg-[#e8aa62] p-0.5">
                    <img src="{{ asset('images/career-ai-meerkat-walk.png') }}" alt="" class="size-full object-contain">
                </div>
                <div>
                    <p class="text-sm font-semibold tracking-tight">Career AI</p>
                    <p class="mobile-tagline text-[11px] text-[#80684f]">就活を、ひとつずつ前へ。</p>
                </div>
            </div>
            <div class="login-scene relative -mx-5 mt-2 flex h-28 shrink-0 items-center gap-2 px-5 sm:-mx-10 sm:h-36 lg:hidden">
                <p class="flex w-28 shrink-0 items-center gap-2 text-[11px] font-medium leading-5 text-[#66513e]">
                    <span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#e8aa62] text-[#573a2e]" aria-hidden="true">✦</span>
                    <span>今の自分から、<br>次の一歩へ。</span>
                </p>
                <div class="relative h-full min-w-0 flex-1 overflow-hidden" aria-hidden="true">
                    <svg class="absolute right-0 top-1 size-10" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                        <g fill="none" stroke="#80583d" stroke-linecap="round" stroke-width="3.5"><circle cx="32" cy="32" r="15" fill="#f2c875"/><path d="M32 6v7 M32 51v7 M6 32h7 M51 32h7 M13.5 13.5l5 5 M45.5 45.5l5 5 M50.5 13.5l-5 5 M18.5 45.5l-5 5"/></g>
                    </svg>
                    <svg class="ground-scroll absolute bottom-2 left-0 h-6 w-[200%]" viewBox="0 0 640 32" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 17 Q28 13 54 17 T108 17 Q137 21 162 16 T216 17 Q243 13 270 17 T320 17 M320 17 Q348 13 374 17 T428 17 Q457 21 482 16 T536 17 Q563 13 590 17 T640 17" fill="none" stroke="#80583d" stroke-linecap="round" stroke-width="3"/>
                    </svg>
                    <div class="meerkat-journey absolute bottom-2 left-[8%] h-[68px] w-[60px]">
                        <img src="{{ asset('images/career-ai-meerkat-walk.png') }}" alt="" class="meerkat-sprite h-full w-full object-contain object-bottom" />
                    </div>
                </div>
            </div>

            <div class="login-main mx-auto flex min-h-0 w-full max-w-md flex-1 flex-col justify-center rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3] px-5 py-3 shadow-[0_18px_55px_rgba(99,68,39,0.08)] sm:px-8 lg:my-auto lg:flex-none lg:px-7 lg:py-8">
                <div class="login-heading mb-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#a66c48]">Welcome back</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight sm:text-3xl">ログイン</h2>
                    <p class="mt-1 text-[13px] leading-5 text-[#766553]">登録したメールアドレスとPINを入力してください。</p>
                </div>
                @if ($errors->any())<p role="alert" class="mb-3 rounded-xl bg-[#fbf0e6] px-3 py-2 text-xs font-medium text-[#98553c]">{{ $errors->first() }}</p>@endif
                <form method="POST" action="{{ route('login.authenticate') }}" class="login-form flex flex-col gap-3.5" autocomplete="on">@csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium">メールアドレス</label>
                        <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" placeholder="name@example.com" required class="login-input w-full rounded-xl border border-[#dce4de] bg-white px-4 py-3 text-sm outline-none transition placeholder:text-[#a1aca5] focus:border-[#477565] focus:ring-4 focus:ring-[#477565]/10">
                    </div>
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-3">
                            <label for="pin" class="block text-sm font-medium">ログインPIN</label>
                            <span class="text-xs text-[#95816c]">数字4桁または6桁</span>
                        </div>
                        <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="current-password" minlength="4" maxlength="6" pattern="[0-9]{4}|[0-9]{6}" placeholder="••••••" required class="login-input w-full rounded-xl border border-[#dce4de] bg-white px-4 py-3 text-sm tracking-[0.3em] outline-none transition placeholder:text-[#b4beb7] focus:border-[#477565] focus:ring-4 focus:ring-[#477565]/10">
                        <p class="mt-1.5 text-[11px] leading-4 text-[#95816c]">招待されたアカウントのPINを入力してください。</p>
                    </div>
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#744c37] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#8a5c40] focus:outline-none focus:ring-4 focus:ring-[#b98055]/20">
                        ログイン <span aria-hidden="true">→</span>
                    </button>
                </form>
                @if (app()->environment('local') || config('services.career_ai.demo_login_enabled'))
                    <form method="POST" action="{{ route('demo.login') }}" class="mt-3">@csrf<button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#dfcbb0] bg-[#f8efdf] px-4 py-3 text-sm font-semibold text-[#744c37] transition hover:bg-[#f3e5cf]">デモでログイン <span aria-hidden="true">→</span></button></form>
                @endif
                <div class="invite-note my-4 flex items-center gap-3 text-[11px] text-[#a0aaa3]">
                    <span class="h-px flex-1 bg-[#e4e9e5]"></span><span>招待制</span><span class="h-px flex-1 bg-[#e4e9e5]"></span>
                </div>
                <p class="text-center text-[12px] leading-5 text-[#766553]">アカウント登録には招待リンクが必要です。<br>招待を受け取った方は、リンクから登録できます。</p>
            </div>
            <footer class="shrink-0 pt-2 text-center text-[10px] text-[#9a8874]">Career AI <span aria-hidden="true">·</span> 就活の準備を、ひとつの場所に</footer>
        </section>
    </main>
</body>
</html>
