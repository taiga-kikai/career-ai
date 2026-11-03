<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait OwnedByCurrentUser
{
    protected static function bootOwnedByCurrentUser(): void
    {
        static::addGlobalScope('owner', function (Builder $query): void {
            $model = $query->getModel();
            $query->where($model->qualifyColumn('user_id'), Auth::id());
        });

        static::creating(function (Model $model): void {
            if (! $model->user_id && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }
}
