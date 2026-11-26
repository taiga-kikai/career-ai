<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'category', 'company', 'company_profile_id', 'program', 'status', 'detail', 'result_date', 'compensation_summary', 'compensation_researched_at'])]
/** 企業への応募記録を表し、書類・面接準備・予定・プロフィールとの関連を持つ。 */
class CareerApplication extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    /** DBの日付やJSON列をPHPで扱いやすい型に変換する。 */
    protected function casts(): array
    {
        return ['result_date' => 'date', 'compensation_summary' => 'array', 'compensation_researched_at' => 'datetime'];
    }

    /** この応募記録を所有する利用者を返す。 */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 比較用の企業プロフィールを返す。 */
    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id');
    }

    /** この応募先に保存されたES・書類を返す。 */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'career_application_id');
    }

    /** この応募先に紐づく面接準備を返す。 */
    public function interviewPreparation(): HasOne
    {
        return $this->hasOne(InterviewPreparation::class, 'career_application_id');
    }

    /** 旧企業研究画面の保存データとの関連を返す。 */
    public function research(): HasOne
    {
        return $this->hasOne(CompanyResearch::class, 'career_application_id');
    }

    /** この応募先に紐づく選考予定を返す。 */
    public function scheduleEvents(): HasMany
    {
        return $this->hasMany(ScheduleEvent::class, 'career_application_id');
    }
}
