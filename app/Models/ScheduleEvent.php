<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'company_name', 'selection_type', 'confirmation_status', 'event_type', 'title', 'meeting_format', 'starts_at', 'ends_at', 'location', 'meeting_url', 'notes', 'is_completed', 'google_calendar_event_id'])]
class ScheduleEvent extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_completed' => 'boolean'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }
}
