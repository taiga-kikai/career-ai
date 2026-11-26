<?php

namespace App\Http\Middleware;

use App\Services\NeonActivityEstimateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** 認証済み画面の応答後に、Neon利用時間の概算記録を追加する。 */
class TrackNeonUsageEstimate
{
    /** 通常の画面処理を先に完了させ、ログイン利用者の利用だけを推定サービスへ記録する。 */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            app(NeonActivityEstimateService::class)->recordActivity();
        }

        return $response;
    }
}
