<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'google_calendar_refresh_token'])]
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_calendar_refresh_token' => 'encrypted',
        ];
    }

    public function careerApplications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    public function researchConversations(): HasMany
    {
        return $this->hasMany(ResearchConversation::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(UserInvitation::class, 'created_by');
    }
}
