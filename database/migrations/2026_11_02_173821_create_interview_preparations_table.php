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
        Schema::create('interview_preparations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('self_introduction')->nullable();
            $table->text('motivation')->nullable();
            $table->text('student_experience')->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('questions_to_ask')->nullable();
            $table->json('practice_questions')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_preparations');
    }
};
