<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->unsignedInteger('initial_salary_yen')->nullable();
            $table->unsignedInteger('average_annual_salary_yen')->nullable();
            $table->unsignedInteger('average_salary_30s_yen')->nullable();
            $table->decimal('average_overtime_hours', 6, 1)->nullable();
            $table->unsignedSmallInteger('annual_holidays_days')->nullable();
            $table->decimal('paid_leave_rate', 5, 1)->nullable();
            $table->unsignedInteger('housing_allowance_yen')->nullable();
            $table->text('benefits')->nullable();
            $table->text('work_locations')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'company_name']);
        });

        Schema::table('career_applications', function (Blueprint $table): void {
            $table->foreignId('company_profile_id')->nullable()->after('company')->constrained('company_profiles')->nullOnDelete();
            $table->index('company_profile_id');
        });

        DB::table('career_applications')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->chunkById(200, function ($applications): void {
                foreach ($applications as $application) {
                    DB::table('company_profiles')->insertOrIgnore([
                        'user_id' => $application->user_id,
                        'company_name' => trim($application->company),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $profileId = DB::table('company_profiles')
                        ->where('user_id', $application->user_id)
                        ->where('company_name', trim($application->company))
                        ->value('id');

                    if ($profileId) {
                        DB::table('career_applications')
                            ->where('id', $application->id)
                            ->update(['company_profile_id' => $profileId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('career_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('company_profile_id');
        });

        Schema::dropIfExists('company_profiles');
    }
};
