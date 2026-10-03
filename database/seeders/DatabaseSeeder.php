<?php

namespace Database\Seeders;

use App\Enums\AccessRequestStatus;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Models\AccessRequest;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with demo data for local development
     * and for evaluators to explore both roles with realistic content.
     */
    public function run(): void
    {
        $ti = User::factory()->create([
            'name' => 'Lillyan Cardoso Zalamena',
            'email' => 'lillycardoso02@gmail.com',
            'role' => UserRole::Ti,
        ]);

        $otherTi = User::factory()->create([
            'name' => 'TI Demo',
            'email' => 'ti@flowkly.test',
            'role' => UserRole::Ti,
        ]);

        $user = User::factory()->create([
            'name' => 'Usuário Demo',
            'email' => 'usuario@flowkly.test',
            'role' => UserRole::User,
        ]);

        $tickets = [
            [
                'title' => 'Computador não liga',
                'description' => 'O computador não inicia desde hoje de manhã. Já verifiquei a tomada e o cabo de energia, mas o problema persiste.',
                'type' => TicketType::Hardware,
                'status' => TicketStatus::Aberta,
                'assigned_to' => $ti->id,
            ],
            [
                'title' => 'Acesso ao sistema financeiro',
                'description' => 'Preciso de acesso ao sistema financeiro para fechar o relatório do mês.',
                'type' => TicketType::Acesso,
                'status' => TicketStatus::EmAndamento,
                'assigned_to' => $ti->id,
            ],
            [
                'title' => 'Instalação de software de edição',
                'description' => 'Gostaria de solicitar a instalação do pacote de edição de imagens na minha máquina.',
                'type' => TicketType::Software,
                'status' => TicketStatus::EmAndamento,
                'assigned_to' => $otherTi->id,
            ],
            [
                'title' => 'Troca de monitor',
                'description' => 'O monitor está com a tela piscando constantemente, já não é mais possível trabalhar normalmente.',
                'type' => TicketType::Hardware,
                'status' => TicketStatus::Concluida,
                'assigned_to' => $ti->id,
            ],
            [
                'title' => 'Lentidão na rede do setor',
                'description' => 'A internet do setor está muito lenta desde ontem à tarde, atrapalhando videochamadas.',
                'type' => TicketType::Rede,
                'status' => TicketStatus::Aberta,
                'assigned_to' => $otherTi->id,
            ],
        ];

        foreach ($tickets as $data) {
            $ticket = Ticket::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
                'status' => $data['status'],
                'user_id' => $user->id,
                'assigned_to' => $data['assigned_to'],
            ]);

            $ticket->statusHistories()->create([
                'from_status' => null,
                'to_status' => TicketStatus::Aberta,
                'changed_by' => $user->id,
            ]);

            if ($data['status'] !== TicketStatus::Aberta) {
                $ticket->statusHistories()->create([
                    'from_status' => TicketStatus::Aberta,
                    'to_status' => $data['status'],
                    'changed_by' => $data['assigned_to'],
                ]);
            }
        }

        AccessRequest::create([
            'name' => 'Novo Colaborador',
            'email' => 'novo.colaborador@flowkly.test',
            'reason' => 'Acabei de ser contratado e preciso de acesso ao portal para abrir solicitações para o setor de TI.',
            'status' => AccessRequestStatus::Pending,
        ]);
    }
}
