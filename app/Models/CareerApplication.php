<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'category', 'company', 'program', 'status', 'detail', 'result_date', 'compensation_summary', 'compensation_researched_at'])]
class CareerApplication extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    protected function casts(): array
    {
        return ['result_date' => 'date', 'compensation_summary' => 'array', 'compensation_researched_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'career_application_id');
    }

    public function interviewPreparation(): HasOne
    {
        return $this->hasOne(InterviewPreparation::class, 'career_application_id');
    }

    public function research(): HasOne
    {
        return $this->hasOne(CompanyResearch::class, 'career_application_id');
    }

    public function scheduleEvents(): HasMany
    {
        return $this->hasMany(ScheduleEvent::class, 'career_application_id');
    }
}
