<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Eloquentモデルの検索・新規作成を現在のログイン利用者に結び付ける共通処理。 */
trait OwnedByCurrentUser
{
    /** 全検索へ所有者条件を付け、ログイン中の新規レコードには所有者IDを設定する。 */
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
