<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'self_introduction', 'motivation', 'student_experience', 'strengths', 'weaknesses', 'questions_to_ask', 'practice_questions', 'notes'])]
class InterviewPreparation extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    protected function casts(): array
    {
        return ['practice_questions' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }
}
