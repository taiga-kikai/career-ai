<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Artisanのdb:seedから呼び出すSeederの入口。 */
class DatabaseSeeder extends Seeder
{
    /** 現在利用する応募サンプルSeederへ処理を渡す。 */
    public function run(): void
    {
        $this->call(CareerApplicationSeeder::class);
    }
}
