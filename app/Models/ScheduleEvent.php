<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'company_name', 'selection_type', 'confirmation_status', 'event_type', 'title', 'meeting_format', 'starts_at', 'ends_at', 'location', 'meeting_url', 'notes', 'is_completed', 'google_calendar_event_id'])]
/** 面接・説明会・締切などの予定と、任意のGoogleイベントIDを表す。 */
class ScheduleEvent extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    /** 日時列と完了状態を、PHPの日時・真偽値として扱う。 */
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_completed' => 'boolean'];
    }

    /** この予定と関連付けられている応募先を返す。 */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }
}
