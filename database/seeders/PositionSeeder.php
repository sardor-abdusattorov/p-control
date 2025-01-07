<?php

namespace Database\Seeders;

use App\Enums\PositionEnum;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PositionEnum::cases() as $positionEnum) {
            Position::firstOrCreate(
                ['name' => $positionEnum->value],
                ['name' => $positionEnum->value]
            );
        }
    }
}
