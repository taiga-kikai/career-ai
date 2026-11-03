<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('career_application_id');
            $table->string('selection_type', 20)->default('main')->after('company_name');
            $table->string('confirmation_status', 20)->default('planned')->after('selection_type');
        });

        DB::table('schedule_events')
            ->join('career_applications', 'schedule_events.career_application_id', '=', 'career_applications.id')
            ->update([
                'schedule_events.company_name' => DB::raw('career_applications.company'),
                'schedule_events.selection_type' => DB::raw("CASE WHEN career_applications.category = 'main' THEN 'main' ELSE 'internship' END"),
            ]);
    }

    public function down(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'selection_type', 'confirmation_status']);
        });
    }
};
