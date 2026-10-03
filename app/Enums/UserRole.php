<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Ti = 'ti';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuário',
            self::Ti => 'TI',
        };
    }

    public function homeRouteName(): string
    {
        return match ($this) {
            self::User => 'requests.index',
            self::Ti => 'dashboard',
        };
    }
}
