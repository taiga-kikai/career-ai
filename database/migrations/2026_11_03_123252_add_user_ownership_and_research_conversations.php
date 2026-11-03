<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $demoUserId = DB::table('users')->where('email', 'demo@career-ai.local')->value('id');
        if (! $demoUserId) {
            $demoUserId = DB::table('users')->insertGetId([
                'name' => 'デモアカウント',
                'email' => 'demo@career-ai.local',
                'password' => Hash::make(Str::random(64)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $columnsByTable = [
            'career_applications' => ['user_id', 'compensation_summary', 'compensation_researched_at'],
            'application_status_options' => ['user_id'],
            'schedule_events' => ['user_id'],
            'application_documents' => ['user_id'],
            'interview_preparations' => ['user_id'],
            'company_researches' => ['user_id'],
        ];

        foreach ($columnsByTable as $tableName => $columns) {
            foreach ($columns as $columnName) {
                if (Schema::hasColumn($tableName, $columnName)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($columnName): void {
                    if ($columnName === 'user_id') {
                        $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                    } elseif ($columnName === 'compensation_summary') {
                        $table->json('compensation_summary')->nullable();
                    } else {
                        $table->timestamp('compensation_researched_at')->nullable();
                    }
                });
            }
        }

        foreach (['career_applications', 'application_status_options', 'schedule_events', 'application_documents', 'interview_preparations', 'company_researches'] as $tableName) {
            DB::table($tableName)->whereNull('user_id')->update(['user_id' => $demoUserId]);
        }

        $indexNames = collect(Schema::getIndexes('application_status_options'))->pluck('name');
        if ($indexNames->contains('application_status_options_category_status_group_label_unique')) {
            Schema::table('application_status_options', function (Blueprint $table): void {
                $table->dropUnique('application_status_options_category_status_group_label_unique');
            });
        }
        if (! $indexNames->contains('app_status_owner_group_label_unique')) {
            Schema::table('application_status_options', function (Blueprint $table): void {
                $table->unique(['user_id', 'category', 'status_group', 'label'], 'app_status_owner_group_label_unique');
            });
        }

        if (! Schema::hasTable('research_conversations')) {
            Schema::create('research_conversations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('career_application_id')->nullable()->constrained()->nullOnDelete();
                $table->string('company_name', 255);
                $table->string('category', 20)->nullable();
                $table->string('title', 255);
                $table->json('messages');
                $table->timestamps();
                $table->index(['user_id', 'updated_at']);
            });
        }

        if (! Schema::hasTable('user_invitations')) {
            Schema::create('user_invitations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->char('token_hash', 64)->unique();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
        Schema::dropIfExists('research_conversations');

        if (Schema::hasTable('application_status_options')) {
            $indexNames = collect(Schema::getIndexes('application_status_options'))->pluck('name');
            if ($indexNames->contains('app_status_owner_group_label_unique')) {
                Schema::table('application_status_options', function (Blueprint $table): void {
                    $table->dropUnique('app_status_owner_group_label_unique');
                });
            }
            if (! $indexNames->contains('application_status_options_category_status_group_label_unique')) {
                Schema::table('application_status_options', function (Blueprint $table): void {
                    $table->unique(['category', 'status_group', 'label']);
                });
            }
        }

        foreach (['application_status_options', 'schedule_events', 'application_documents', 'interview_preparations', 'company_researches', 'career_applications'] as $tableName) {
            if (Schema::hasColumn($tableName, 'user_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('user_id');
                });
            }
        }
        if (Schema::hasColumn('career_applications', 'compensation_summary')) {
            Schema::table('career_applications', fn (Blueprint $table) => $table->dropColumn('compensation_summary'));
        }
        if (Schema::hasColumn('career_applications', 'compensation_researched_at')) {
            Schema::table('career_applications', fn (Blueprint $table) => $table->dropColumn('compensation_researched_at'));
        }
    }
};
