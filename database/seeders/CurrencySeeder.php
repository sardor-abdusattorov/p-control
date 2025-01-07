<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        DB::table('currency')->insert([
            [
                'name'       => 'Сум',
                'short_name' => 'UZS',
                'value'      => 1,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Рубль',
                'short_name' => 'RUB',
                'value'      => 1,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Евро',
                'short_name' => 'EUR',
                'value'      => 1,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Доллар',
                'short_name' => 'USD',
                'value'      => 1,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
