<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::truncate();

        Language::create([
            'locale' => 'ru',
            'name' => 'Русский',
            'is_default' => true,
            'status' => true,
        ]);

        Language::create([
            'locale' => 'uz',
            'name' => 'O‘zbekcha',
            'is_default' => false,
            'status' => true,
        ]);

        Language::create([
            'locale' => 'en',
            'name' => 'English',
            'is_default' => false,
            'status' => true,
        ]);
    }
}
