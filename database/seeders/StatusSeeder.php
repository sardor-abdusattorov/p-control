<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run()
    {
        Status::create(['name' => 'Новый']);
        Status::create(['name' => 'Отправлен']);
        Status::create(['name' => 'Отказан']);
        Status::create(['name' => 'Одобрен']);
        Status::create(['name' => 'В процессе']);
        Status::create(['name' => 'Завершен']);
    }
}
