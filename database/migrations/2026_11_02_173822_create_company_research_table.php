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
        Schema::create('company_researches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('industry')->nullable();
            $table->longText('business_outline')->nullable();
            $table->longText('products_services')->nullable();
            $table->longText('strengths')->nullable();
            $table->longText('competitors')->nullable();
            $table->longText('culture')->nullable();
            $table->longText('recent_topics')->nullable();
            $table->longText('motivation')->nullable();
            $table->json('sources')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_researches');
    }
};
