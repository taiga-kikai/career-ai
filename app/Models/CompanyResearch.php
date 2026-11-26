<?php

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'career_application_id', 'industry', 'business_outline', 'products_services', 'strengths', 'competitors', 'culture', 'recent_topics', 'motivation', 'sources', 'notes'])]
/** 旧企業研究AI機能の記録を保持する互換用モデル。現在の企業比較とは別の旧データ。 */
class CompanyResearch extends Model
{
    use HasFactory;
    use OwnedByCurrentUser;

    protected $table = 'company_researches';

    /** 調査元一覧のJSON列をPHP配列として読み書きする。 */
    protected function casts(): array
    {
        return ['sources' => 'array'];
    }

    /** 旧企業研究記録が紐づいていた応募先を返す。 */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }
}
