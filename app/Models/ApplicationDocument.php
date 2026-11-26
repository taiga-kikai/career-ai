<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'title', 'raw_text', 'questions'])]
/** 応募先に紐づくES原文と、分割済みの設問・回答を表す。 */
class ApplicationDocument extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    /** 設問と回答を保存するJSON列をPHP配列として読み書きする。 */
    protected function casts(): array
    {
        return ['questions' => 'array'];
    }

    /** この書類を提出する応募先を返す。 */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }
}
