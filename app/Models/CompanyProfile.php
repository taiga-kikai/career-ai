<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'company_name',
    'initial_salary_yen',
    'average_annual_salary_yen',
    'average_salary_30s_yen',
    'average_overtime_hours',
    'annual_holidays_days',
    'paid_leave_rate',
    'housing_allowance_yen',
    'benefits',
    'work_locations',
    'notes',
])]
class CompanyProfile extends Model
{
    use OwnedByCurrentUser;

    protected function casts(): array
    {
        return [
            'initial_salary_yen' => 'integer',
            'average_annual_salary_yen' => 'integer',
            'average_salary_30s_yen' => 'integer',
            'average_overtime_hours' => 'decimal:1',
            'annual_holidays_days' => 'integer',
            'paid_leave_rate' => 'decimal:1',
            'housing_allowance_yen' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CareerApplication::class, 'company_profile_id');
    }
}
