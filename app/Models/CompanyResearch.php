<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'industry', 'business_outline', 'products_services', 'strengths', 'competitors', 'culture', 'recent_topics', 'motivation', 'sources', 'notes'])]
class CompanyResearch extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    protected $table = 'company_researches';

    protected function casts(): array
    {
        return ['sources' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }
}
