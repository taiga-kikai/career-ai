<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['created_by', 'token_hash', 'expires_at', 'used_at'])]
/** アカウント作成に使う招待トークンのハッシュ・期限・利用状態を表す。 */
class UserInvitation extends Model
{
    /** 期限と使用日時をCarbon日時として扱う。 */
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    }

    /** この招待リンクを作成した利用者を返す。 */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
