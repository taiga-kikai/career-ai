<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $demoUserId = DB::table('users')->where('email', 'demo@career-ai.local')->value('id');
        $ownedTables = [
            'career_applications',
            'application_status_options',
            'schedule_events',
            'application_documents',
            'interview_preparations',
            'company_researches',
            'research_conversations',
        ];

        foreach ($ownedTables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'user_id')) {
                continue;
            }

            if (DB::table($tableName)->whereNull('user_id')->exists()) {
                if (! $demoUserId) {
                    throw new RuntimeException("Cannot assign owner for unowned records in {$tableName}.");
                }

                DB::table($tableName)->whereNull('user_id')->update(['user_id' => $demoUserId]);
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'career_applications',
            'application_status_options',
            'schedule_events',
            'application_documents',
            'interview_preparations',
            'company_researches',
            'research_conversations',
        ] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'user_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }
};
