<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('career_applications', 'result_date')) {
            Schema::table('career_applications', function (Blueprint $table): void {
                $table->date('result_date')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('career_applications', 'result_date')) {
            Schema::table('career_applications', fn (Blueprint $table) => $table->dropColumn('result_date'));
        }
    }
};
