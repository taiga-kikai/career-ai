<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class NeonUsageService
{
    /**
     * Return infrastructure usage only to the configured owner account.
     *
     * @return array<string, mixed>|null
     */
    public function forUser(User $user): ?array
    {
        $ownerEmail = mb_strtolower(trim((string) config('services.neon.owner_email')));

        if ($ownerEmail === '' || ! hash_equals($ownerEmail, mb_strtolower($user->email))) {
            return null;
        }

        $plan = mb_strtolower((string) config('services.neon.plan', 'free'));
        $storageLimit = $plan === 'free'
            ? (int) config('services.neon.storage_limit_bytes', 1_073_741_824)
            : null;

        $usage = [
            'connected' => false,
            'plan' => $plan,
            'storage_used_bytes' => null,
            'storage_limit_bytes' => $storageLimit,
            'storage_remaining_bytes' => null,
            'storage_percent' => null,
            'compute_status' => $plan === 'free' ? 'free_unavailable' : 'not_configured',
            'compute_used_cu_hours' => null,
            'compute_limit_cu_hours' => $plan === 'free' ? (float) config('services.neon.monthly_cu_limit', 100) : null,
            'compute_remaining_cu_hours' => null,
            'estimated_active_hours_remaining' => null,
            'updated_at' => now(),
            'console_url' => 'https://console.neon.tech/',
        ];

        $connection = DB::connection();

        if ($connection->getDriverName() !== 'pgsql') {
            $usage['message'] = 'Neon PostgreSQLへ接続すると保存量を表示します。';
            $usage['compute_status'] = 'database_unavailable';

            return $usage;
        }

        try {
            $database = $connection->selectOne('select pg_database_size(current_database()) as size_bytes');
            $storageUsed = (int) ($database->size_bytes ?? 0);

            $usage['connected'] = true;
            $usage['storage_used_bytes'] = $storageUsed;
            $usage['storage_remaining_bytes'] = $storageLimit === null
                ? null
                : max(0, $storageLimit - $storageUsed);
            $usage['storage_percent'] = $storageLimit === null || $storageLimit <= 0
                ? null
                : min(100, round(($storageUsed / $storageLimit) * 100, 1));
        } catch (Throwable) {
            $usage['message'] = 'DBの保存量を取得できませんでした。Neonの管理画面をご確認ください。';
            $usage['compute_status'] = 'database_unavailable';

            return $usage;
        }

        if ($plan === 'free') {
            $usage['message'] = 'FreeプランではNeonの使用量APIが利用できないため、CU使用量はNeon管理画面で確認してください。';

            return $usage;
        }

        $apiKey = trim((string) config('services.neon.api_key'));
        $orgId = trim((string) config('services.neon.org_id'));
        $projectId = trim((string) config('services.neon.project_id'));

        if ($apiKey === '' || $orgId === '' || $projectId === '') {
            $usage['message'] = 'CUの自動表示にはNeonの有料プランと管理用API設定が必要です。';
            $usage['compute_status'] = 'not_configured';

            return $usage;
        }

        $monthStart = now('UTC')->startOfMonth();
        $cacheKey = 'neon-usage:'.hash('sha256', $projectId.':'.$monthStart->format('Y-m'));

        try {
            $computeSeconds = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($apiKey, $orgId, $projectId, $monthStart): float {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(8)
                    ->get('https://console.neon.tech/api/v2/consumption_history/v2/projects', [
                        'from' => $monthStart->toIso8601String(),
                        'to' => now('UTC')->toIso8601String(),
                        'granularity' => 'daily',
                        'org_id' => $orgId,
                        'project_ids' => $projectId,
                        'metrics' => 'compute_unit_seconds',
                    ])
                    ->throw()
                    ->json();

                $seconds = 0.0;

                foreach ($response['projects'] ?? [] as $project) {
                    if (($project['project_id'] ?? null) !== $projectId) {
                        continue;
                    }

                    foreach ($project['periods'] ?? [] as $period) {
                        foreach ($period['consumption'] ?? [] as $timeframe) {
                            foreach ($timeframe['metrics'] ?? [] as $metric) {
                                if (($metric['metric_name'] ?? null) === 'compute_unit_seconds') {
                                    $seconds += (float) ($metric['value'] ?? 0);
                                }
                            }
                        }
                    }
                }

                return $seconds;
            });

            $usedCuHours = round($computeSeconds / 3600, 2);
            $limit = $usage['compute_limit_cu_hours'];

            $usage['compute_status'] = 'available';
            $usage['compute_used_cu_hours'] = $usedCuHours;
            $usage['compute_remaining_cu_hours'] = $limit === null ? null : max(0, round($limit - $usedCuHours, 2));
            $usage['estimated_active_hours_remaining'] = $limit === null
                ? null
                : round(max(0, $limit - $usedCuHours) / 0.25, 1);
            $usage['updated_at'] = now();
            $usage['message'] = $limit === null
                ? 'CU時間は累計表示です。有料プランではFreeの月100 CU時間上限は適用されません。'
                : 'Neonの使用量APIは約15分ごとに更新されます。0.25 CUで動いた場合の残り稼働時間を目安表示しています。';
        } catch (Throwable) {
            $usage['compute_status'] = 'unavailable';
            $usage['message'] = 'NeonからCU使用量を取得できませんでした。管理画面をご確認ください。';
        }

        return $usage;
    }
}
