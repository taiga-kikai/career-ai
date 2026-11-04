<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('schedule_events', 'meeting_format')) {
            Schema::table('schedule_events', function (Blueprint $table): void {
                $table->string('meeting_format', 20)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schedule_events', 'meeting_format')) {
            Schema::table('schedule_events', fn (Blueprint $table) => $table->dropColumn('meeting_format'));
        }
    }
};
