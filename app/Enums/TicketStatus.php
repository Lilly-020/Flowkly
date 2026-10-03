<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Aberta = 'aberta';
    case EmAndamento = 'em_andamento';
    case Concluida = 'concluida';

    public function label(): string
    {
        return match ($this) {
            self::Aberta => 'Aberta',
            self::EmAndamento => 'Em andamento',
            self::Concluida => 'Concluída',
        };
    }
}
