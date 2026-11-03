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

class InvitationController extends Controller
{
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

    public function accept(string $token): View
    {
        $invitation = $this->validInvitation($token);

        return view('auth.accept-invitation', ['invitation' => $invitation, 'token' => $token, 'email' => '']);
    }

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
