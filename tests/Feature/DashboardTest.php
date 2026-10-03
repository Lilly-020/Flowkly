<?php

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

test('ti dashboard shows stats scoped to tickets assigned to the logged-in ti user', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $otherTi = User::factory()->create(['role' => UserRole::Ti]);

    Ticket::factory()->create([
        'assigned_to' => $ti->id,
        'status' => TicketStatus::Aberta,
        'type' => TicketType::Hardware,
    ]);
    Ticket::factory()->create([
        'assigned_to' => $ti->id,
        'status' => TicketStatus::EmAndamento,
        'type' => TicketType::Software,
    ]);
    Ticket::factory()->create([
        'assigned_to' => $ti->id,
        'status' => TicketStatus::Concluida,
        'type' => TicketType::Software,
    ]);
    // Belongs to someone else — must not be counted.
    Ticket::factory()->create(['assigned_to' => $otherTi->id]);

    $this->actingAs($ti)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total', 3)
            ->where('stats.aberta', 1)
            ->where('stats.em_andamento', 1)
            ->where('stats.concluida', 1)
            ->has('categories', 2)
            ->has('recentTickets', 3)
        );
});

test('a regular user cannot access the dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->get('/dashboard')->assertForbidden();
});
