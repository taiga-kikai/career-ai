<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferCareerAiAccount extends Command
{
    protected $signature = 'career-ai:transfer-account {email : Email address of the local account to transfer}';

    protected $description = 'Copy one local MySQL account and its private career data to PostgreSQL';

    private const TARGET_CONNECTION = 'career_ai_transfer';

    /**
     * @var array<int, string>
     */
    private const USER_OWNED_TABLES = [
        'career_applications',
        'application_status_options',
        'schedule_events',
        'application_documents',
        'interview_preparations',
        'company_researches',
        'research_conversations',
    ];

    public function handle(): int
    {
        $source = DB::connection();
        $target = DB::connection(self::TARGET_CONNECTION);
        $email = mb_strtolower((string) $this->argument('email'));

        if ($source->getDriverName() !== 'mysql') {
            $this->error('移行元のDB_CONNECTIONをmysqlにしてください。');

            return self::FAILURE;
        }

        if (! config('database.connections.'.self::TARGET_CONNECTION.'.url')) {
            $this->error('移行先URLが未設定です。.envにCAREER_AI_TRANSFER_DB_URLを設定してください。');

            return self::FAILURE;
        }

        if ($target->getDriverName() !== 'pgsql' || ! Schema::connection(self::TARGET_CONNECTION)->hasTable('migrations')) {
            $this->error('移行先PostgreSQLにアプリのテーブルがありません。RenderでDB接続後、マイグレーションが完了してから実行してください。');

            return self::FAILURE;
        }

        $targetSchema = Schema::connection(self::TARGET_CONNECTION);
        foreach (['users', ...self::USER_OWNED_TABLES] as $tableName) {
            if (! $targetSchema->hasTable($tableName)) {
                $this->error('移行先のテーブルが不足しています。Renderのマイグレーション完了後に再実行してください。');

                return self::FAILURE;
            }
        }
        $sourceUser = $source->table('users')->where('email', $email)->first();
        if (! $sourceUser) {
            $this->error('ローカルMySQLに指定アカウントが見つかりません。');

            return self::FAILURE;
        }

        if ($target->table('users')->where('email', $email)->exists()) {
            $this->error('移行先に同じメールアドレスのアカウントがあります。重複防止のため中止しました。');

            return self::FAILURE;
        }

        $counts = [];
        $target->transaction(function () use ($source, $target, $sourceUser, &$counts): void {
            $userAttributes = (array) $sourceUser;
            unset($userAttributes['id'], $userAttributes['remember_token'], $userAttributes['google_calendar_refresh_token']);
            $targetUserId = $target->table('users')->insertGetId($userAttributes);

            $applicationIdMap = $this->copyApplications($source, $target, (int) $sourceUser->id, (int) $targetUserId);
            $counts['career_applications'] = count($applicationIdMap);

            foreach (array_slice(self::USER_OWNED_TABLES, 1) as $tableName) {
                $counts[$tableName] = $this->copyOwnedRows(
                    $source,
                    $target,
                    $tableName,
                    (int) $sourceUser->id,
                    (int) $targetUserId,
                    $applicationIdMap,
                );
            }
        });

        $this->info('アカウントと紐づくデータをPostgreSQLへ移しました。');
        foreach ($counts as $tableName => $count) {
            $this->line($tableName.': '.$count.'件');
        }
        $this->warn('Googleカレンダーの認証情報は暗号化キーが異なるため移していません。移行後に再連携してください。');

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    private function copyApplications(Connection $source, Connection $target, int $sourceUserId, int $targetUserId): array
    {
        $idMap = [];
        $sourceRows = $source->table('career_applications')->where('user_id', $sourceUserId)->orderBy('id')->get();
        $targetColumns = Schema::connection(self::TARGET_CONNECTION)->getColumnListing('career_applications');

        foreach ($sourceRows as $sourceRow) {
            $attributes = array_intersect_key((array) $sourceRow, array_flip($targetColumns));
            unset($attributes['id']);
            $attributes['user_id'] = $targetUserId;
            $sourceId = (int) $sourceRow->id;
            $idMap[$sourceId] = (int) $target->table('career_applications')->insertGetId($attributes);
        }

        return $idMap;
    }

    /**
     * @param  array<int, int>  $applicationIdMap
     */
    private function copyOwnedRows(
        Connection $source,
        Connection $target,
        string $tableName,
        int $sourceUserId,
        int $targetUserId,
        array $applicationIdMap,
    ): int {
        $sourceSchema = Schema::connection($source->getName());
        if (! $sourceSchema->hasTable($tableName) || ! $sourceSchema->hasColumn($tableName, 'user_id')) {
            return 0;
        }

        $targetColumns = Schema::connection(self::TARGET_CONNECTION)->getColumnListing($tableName);
        $sourceRows = $source->table($tableName)->where('user_id', $sourceUserId)->orderBy('id')->get();

        foreach ($sourceRows as $sourceRow) {
            $attributes = array_intersect_key((array) $sourceRow, array_flip($targetColumns));
            unset($attributes['id']);
            $attributes['user_id'] = $targetUserId;

            if (array_key_exists('career_application_id', $attributes) && $attributes['career_application_id'] !== null) {
                $attributes['career_application_id'] = $applicationIdMap[(int) $attributes['career_application_id']] ?? null;
            }

            if ($tableName === 'schedule_events' && array_key_exists('google_calendar_event_id', $attributes)) {
                $attributes['google_calendar_event_id'] = null;
            }
            $target->table($tableName)->insert($attributes);
        }

        return $sourceRows->count();
    }
}
