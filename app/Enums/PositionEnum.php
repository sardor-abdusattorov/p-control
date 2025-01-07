<?php

namespace App\Enums;

enum PositionEnum: string
{
    case ADMIN = "ADMIN";
    case PROJECT_MANAGER = "PROJECT_MANAGER";
    case STAFF = "STAFF";

    public static function names(): array
    {
        return array_column(self::cases(), 'value');
    }
}
