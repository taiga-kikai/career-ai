<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'self_introduction', 'motivation', 'student_experience', 'strengths', 'weaknesses', 'questions_to_ask', 'practice_questions', 'notes'])]
/** 応募先ごとの自己紹介、志望動機、練習質問などの面接準備を表す。 */
class InterviewPreparation extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    /** 練習質問のJSON列をPHP配列として読み書きする。 */
    protected function casts(): array
    {
        return ['practice_questions' => 'array'];
    }

    /** 面接準備の対象となる応募先を返す。 */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }
}
