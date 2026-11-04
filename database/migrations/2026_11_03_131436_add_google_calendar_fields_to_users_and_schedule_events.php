<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'google_calendar_refresh_token')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->text('google_calendar_refresh_token')->nullable();
            });
        }

        if (! Schema::hasColumn('schedule_events', 'google_calendar_event_id')) {
            Schema::table('schedule_events', function (Blueprint $table): void {
                $table->string('google_calendar_event_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('schedule_events', 'google_calendar_event_id')) {
            Schema::table('schedule_events', fn (Blueprint $table) => $table->dropColumn('google_calendar_event_id'));
        }

        if (Schema::hasColumn('users', 'google_calendar_refresh_token')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('google_calendar_refresh_token'));
        }
    }
};
