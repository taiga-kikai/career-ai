<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>招待から登録 | Career AI</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-[#f7efe3] p-4 text-[#3c3026]">
<main class="w-full max-w-md rounded-[1.75rem] border border-[#eadfce] bg-[#fffaf3] p-6 shadow-xl sm:p-8">
<p class="text-[10px] font-semibold tracking-[.16em] text-[#a66c48]">INVITATION</p><h1 class="mt-2 text-2xl font-semibold">Career AIに参加</h1>
<p class="mt-2 text-sm text-[#80684f]">招待リンクからアカウントを作成します。メールアドレスとPINは他のアカウントと共有されません。</p>
@if ($errors->any())<p role="alert" class="mt-4 rounded-xl bg-[#fbf0e6] px-3 py-2 text-sm text-[#98553c]">{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('invitation.store', $token) }}" class="mt-6 space-y-4">@csrf
<label class="block text-sm font-medium">名前<input name="name" required maxlength="255" value="{{ old('name') }}" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 outline-none focus:border-[#c88b64]"></label>
<label class="block text-sm font-medium">メールアドレス<input name="email" type="email" required maxlength="255" value="{{ old('email', $email) }}" class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 outline-none focus:border-[#c88b64]"></label>
<label class="block text-sm font-medium">ログインPIN（数字4桁または6桁）<input name="pin" type="password" inputmode="numeric" pattern="[0-9]{4}|[0-9]{6}" minlength="4" maxlength="6" required class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 outline-none focus:border-[#c88b64]"></label>
<label class="block text-sm font-medium">PINをもう一度入力<input name="pin_confirmation" type="password" inputmode="numeric" pattern="[0-9]{4}|[0-9]{6}" minlength="4" maxlength="6" required class="mt-1.5 w-full rounded-xl border border-[#e8ddce] bg-white px-3.5 py-3 outline-none focus:border-[#c88b64]"></label>
<button class="w-full rounded-xl bg-[#744c37] px-4 py-3 font-semibold text-white hover:bg-[#8a5c40]">登録してログイン</button></form>
</main></body></html>
