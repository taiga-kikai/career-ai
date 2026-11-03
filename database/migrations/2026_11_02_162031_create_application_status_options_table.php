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
        Schema::create('application_status_options', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20);
            $table->string('status_group', 40);
            $table->string('label', 120);
            $table->timestamps();
            $table->unique(['category', 'status_group', 'label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_status_options');
    }
};
