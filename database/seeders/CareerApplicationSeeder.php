<?php

namespace Database\Seeders;

use App\CareerApplicationDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CareerApplicationSeeder extends Seeder
{
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
