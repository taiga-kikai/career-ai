<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/** ログイン画面、PIN認証、デモログイン、ログアウトを扱う。 */
class AuthController extends Controller
{
    /** ログイン済みならホームへ移動し、未ログインならログイン画面を表示する。 */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return to_route('home.preview');
        }

        return view('welcome');
    }

    /** 入力検証と試行回数制限を行ってPINを照合し、成功時にセッションを作り直す。 */
    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'pin' => ['required', 'regex:/^(?:\\d{4}|\\d{6})$/'],
        ]);
        $key = mb_strtolower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 6)) {
            throw ValidationException::withMessages(['email' => '試行回数が多すぎます。しばらくしてから再度お試しください。']);
        }

        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['pin']])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'メールアドレスまたはPINを確認してください。']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return to_route('home.preview');
    }

    /** 開発環境または明示的に有効化された環境だけで共有デモアカウントにログインする。 */
    public function demo(Request $request): RedirectResponse
    {
        abort_unless(app()->environment('local') || config('services.career_ai.demo_login_enabled'), 404);
        $user = User::query()->where('email', 'demo@career-ai.local')->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        return to_route('home.preview');
    }

    /** 認証情報とセッションを破棄してログイン画面へ戻す。 */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('logged_out', 'ログアウトしました。');
    }
}
