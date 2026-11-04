<?php

namespace App\Http\Middleware;

use App\Services\NeonActivityEstimateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackNeonUsageEstimate
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            app(NeonActivityEstimateService::class)->recordActivity();
        }

        return $response;
    }
}
