<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'google_calendar_refresh_token'])]
/** ログイン利用者を表し、所有する就活データとの関連と秘密値の保護を定義する。 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /** PINをハッシュ化し、Google更新トークンを暗号化してDBに保存する。 */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_calendar_refresh_token' => 'encrypted',
        ];
    }

    /** この利用者が登録した応募先を返す。 */
    public function careerApplications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    /** この利用者が登録した企業比較情報を返す。 */
    public function companyProfiles(): HasMany
    {
        return $this->hasMany(CompanyProfile::class);
    }

    /** 旧企業研究機能の会話履歴を返す。 */
    public function researchConversations(): HasMany
    {
        return $this->hasMany(ResearchConversation::class);
    }

    /** この利用者が発行した招待リンクを返す。 */
    public function invitations(): HasMany
    {
        return $this->hasMany(UserInvitation::class, 'created_by');
    }
}
