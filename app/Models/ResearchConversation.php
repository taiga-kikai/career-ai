<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'company_name', 'category', 'title', 'messages'])]
/** 旧企業研究AIの会話履歴を保持する互換用モデル。 */
class ResearchConversation extends Model
{
    use OwnedByCurrentUser;

    /** 会話メッセージのJSON列をPHP配列として読み書きする。 */
    protected function casts(): array
    {
        return ['messages' => 'array'];
    }

    /** 会話に関連付けられた応募先を返す。 */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }

    /** 会話履歴を所有する利用者を返す。 */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
