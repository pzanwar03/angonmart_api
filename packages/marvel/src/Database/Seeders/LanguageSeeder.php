<?php

namespace Marvel\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LanguageSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        DB::table('languages')->updateOrInsert(
            ['language_code' => 'en'],
            [
                'language_name' => 'English',
                'flag' => json_encode(['emoji' => '🇺🇸']),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('languages')->updateOrInsert(
            ['language_code' => 'bn'],
            [
                'language_name' => 'Bangla',
                'flag' => json_encode(['emoji' => '🇧🇩']),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('languages')->whereNotIn('language_code', ['en', 'bn'])->delete();
    }
}
