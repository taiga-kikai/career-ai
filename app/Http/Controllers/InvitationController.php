<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** 招待リンクの発行と、そのリンクを使ったアカウント作成を扱う。 */
class InvitationController extends Controller
{
    /** 期限付きのランダムな招待URLを作り、DBにはトークンそのものではなくハッシュを保存する。 */
    public function create(): RedirectResponse
    {
        abort_if(! app()->environment('local') && auth()->user()->email === 'demo@career-ai.local', 403);

        $token = Str::random(64);
        DB::table('user_invitations')->insert([
            'created_by' => Auth::id(),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('invitation_link', route('invitation.accept', $token));
    }

    /** 有効な招待か確認して、アカウント登録画面を表示する。 */
    public function accept(string $token): View
    {
        $invitation = $this->validInvitation($token);

        return view('auth.accept-invitation', ['invitation' => $invitation, 'token' => $token, 'email' => '']);
    }

    /** PINなどを検証し、招待の再利用を防ぎながらユーザー作成と招待使用済み更新をまとめて行う。 */
    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->validInvitation($token);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'pin' => ['required', 'regex:/^(?:\\d{4}|\\d{6})$/', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($invitation, $validated): User {
            $locked = DB::table('user_invitations')->where('id', $invitation->id)->lockForUpdate()->first();
            if (! $locked || $locked->used_at !== null || now()->greaterThan($locked->expires_at)) {
                throw ValidationException::withMessages(['invitation' => '招待リンクの有効期限が切れているか、すでに使用されています。']);
            }

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['pin'],
            ]);
            DB::table('user_invitations')->where('id', $locked->id)->update(['used_at' => now(), 'updated_at' => now()]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return to_route('home.preview');
    }

    /** 生トークンのハッシュ、有効期限、未使用状態を照合し、有効な招待だけを返す。 */
    private function validInvitation(string $token): object
    {
        $invitation = DB::table('user_invitations')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        abort_unless($invitation, 404);

        return $invitation;
    }
}
