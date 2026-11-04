<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingColumns = collect(['company_name', 'selection_type', 'confirmation_status'])
            ->reject(fn (string $column): bool => Schema::hasColumn('schedule_events', $column))
            ->all();

        if ($missingColumns !== []) {
            Schema::table('schedule_events', function (Blueprint $table) use ($missingColumns): void {
                if (in_array('company_name', $missingColumns, true)) {
                    $table->string('company_name')->nullable();
                }
                if (in_array('selection_type', $missingColumns, true)) {
                    $table->string('selection_type', 20)->default('main');
                }
                if (in_array('confirmation_status', $missingColumns, true)) {
                    $table->string('confirmation_status', 20)->default('planned');
                }
            });
        }

        DB::table('schedule_events')
            ->whereNotNull('career_application_id')
            ->orderBy('id')
            ->get(['id', 'career_application_id', 'company_name'])
            ->each(function (object $event): void {
                $application = DB::table('career_applications')
                    ->where('id', $event->career_application_id)
                    ->first(['company', 'category']);

                if (! $application) {
                    return;
                }

                $updates = [
                    'selection_type' => $application->category === 'main' ? 'main' : 'internship',
                ];
                if ($event->company_name === null) {
                    $updates['company_name'] = $application->company;
                }

                DB::table('schedule_events')->where('id', $event->id)->update($updates);
            });
    }

    public function down(): void
    {
        $existingColumns = collect(['company_name', 'selection_type', 'confirmation_status'])
            ->filter(fn (string $column): bool => Schema::hasColumn('schedule_events', $column))
            ->all();

        if ($existingColumns !== []) {
            Schema::table('schedule_events', fn (Blueprint $table) => $table->dropColumn($existingColumns));
        }
    }
};
