<?php

namespace Database\Seeders;

use App\CareerApplicationDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** 開発・ローカルデモ用の架空応募データを、空のデモアカウントへ一度だけ登録する。 */
class CareerApplicationSeeder extends Seeder
{
    /** 既存データを上書きしないよう件数を確認してからサンプル応募先を登録する。 */
    public function run(): void
    {
        $demoUserId = DB::table('users')->where('email', 'demo@career-ai.local')->value('id');
        if (! $demoUserId || DB::table('career_applications')->where('user_id', $demoUserId)->exists()) {
            return;
        }

        DB::table('career_applications')->insert(array_map(
            fn (array $record): array => [...$record, 'user_id' => $demoUserId, 'created_at' => now(), 'updated_at' => now()],
            CareerApplicationDefaults::records(),
        ));
    }
}
