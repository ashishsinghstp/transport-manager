<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Dispatcher = 'dispatcher';
    case Accountant = 'accountant';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
