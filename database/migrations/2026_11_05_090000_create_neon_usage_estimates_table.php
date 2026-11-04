<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('neon_usage_estimates', function (Blueprint $table): void {
            $table->date('month_start')->primary();
            $table->unsignedBigInteger('estimated_active_seconds')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('neon_usage_estimates');
    }
};
