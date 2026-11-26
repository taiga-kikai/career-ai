<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 選考結果の連絡予定日を応募先に保存する nullable な列を追加・削除します。
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
