<?php

namespace App\Enums;

enum TicketType: string
{
    case Hardware = 'hardware';
    case Software = 'software';
    case Rede = 'rede';
    case Acesso = 'acesso';
    case Outro = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::Hardware => 'Hardware',
            self::Software => 'Software',
            self::Rede => 'Rede',
            self::Acesso => 'Acesso',
            self::Outro => 'Outro',
        };
    }
}
