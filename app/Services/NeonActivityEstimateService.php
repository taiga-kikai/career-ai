<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class NeonActivityEstimateService
{
    /**
     * Record one authenticated app request that used the PostgreSQL connection.
     */
    public function recordActivity(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $now = now('UTC');
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $timestamp = $now->toDateTimeString();
        $suspendSeconds = max(60, (int) config('services.neon.estimated_suspend_seconds', 300));

        try {
            $connection->transaction(function () use ($connection, $monthStart, $now, $timestamp, $suspendSeconds): void {
                $connection->table('neon_usage_estimates')->insertOrIgnore([
                    'month_start' => $monthStart,
                    'estimated_active_seconds' => 0,
                    'last_activity_at' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $row = $connection->table('neon_usage_estimates')
                    ->where('month_start', $monthStart)
                    ->lockForUpdate()
                    ->first();

                $additionalSeconds = 0;

                if ($row?->last_activity_at) {
                    $previous = CarbonImmutable::parse($row->last_activity_at, 'UTC');
                    $gapSeconds = max(0, $now->getTimestamp() - $previous->getTimestamp());
                    $additionalSeconds = min($gapSeconds, $suspendSeconds);
                }

                $connection->table('neon_usage_estimates')
                    ->where('month_start', $monthStart)
                    ->update([
                        'estimated_active_seconds' => (int) ($row->estimated_active_seconds ?? 0) + $additionalSeconds,
                        'last_activity_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
            });
        } catch (Throwable) {
            // Usage estimation must never interrupt regular app requests.
        }
    }

    /** @return array{used_cu_hours: float, remaining_cu_hours: float, percent: float, active_hours_remaining: float, updated_at: CarbonImmutable|null} */
    public function currentMonthEstimate(float $limitCuHours): array
    {
        $now = CarbonImmutable::now('UTC');
        $monthStart = $now->startOfMonth()->toDateString();
        $row = DB::connection()->table('neon_usage_estimates')
            ->where('month_start', $monthStart)
            ->first();

        $assumedCu = max(0.01, (float) config('services.neon.estimated_min_cu', 0.25));
        $suspendSeconds = max(60, (int) config('services.neon.estimated_suspend_seconds', 300));
        $activeSeconds = (int) ($row->estimated_active_seconds ?? 0);

        if ($row?->last_activity_at) {
            $lastActivity = CarbonImmutable::parse($row->last_activity_at, 'UTC');
            $gapSeconds = max(0, $now->getTimestamp() - $lastActivity->getTimestamp());
            $activeSeconds += min($gapSeconds, $suspendSeconds);
        }

        $usedCuHours = round(($activeSeconds * $assumedCu) / 3600, 2);
        $remainingCuHours = max(0, round($limitCuHours - $usedCuHours, 2));

        return [
            'used_cu_hours' => $usedCuHours,
            'remaining_cu_hours' => $remainingCuHours,
            'percent' => $limitCuHours > 0 ? min(100, round(($usedCuHours / $limitCuHours) * 100, 1)) : 0,
            'active_hours_remaining' => round($remainingCuHours / $assumedCu, 1),
            'updated_at' => $row?->last_activity_at
                ? CarbonImmutable::parse($row->last_activity_at, 'UTC')
                : null,
        ];
    }
}
