<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Mail\TicketAssigned;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('a user can create a ticket and the assignee is emailed', function () {
    Mail::fake();
    Storage::fake('public');

    $user = User::factory()->create(['role' => UserRole::User]);
    $ti = User::factory()->create(['role' => UserRole::Ti]);

    $response = $this->actingAs($user)->post('/requests', [
        'title' => 'Computador não liga',
        'description' => 'O computador não inicia desde hoje de manhã.',
        'type' => 'hardware',
        'assigned_to' => $ti->id,
        'images' => [UploadedFile::fake()->image('foto.jpg')],
    ]);

    $response->assertRedirect(route('requests.index'));

    $this->assertDatabaseHas('tickets', [
        'title' => 'Computador não liga',
        'user_id' => $user->id,
        'assigned_to' => $ti->id,
        'status' => TicketStatus::Aberta->value,
    ]);

    $ticket = Ticket::first();
    expect($ticket->attachments)->toHaveCount(1);
    expect($ticket->statusHistories)->toHaveCount(1);

    Mail::assertSent(TicketAssigned::class, fn ($mail) => $mail->hasTo($ti->email));
});

test('a ti user can also create a ticket and gets redirected to the kanban', function () {
    Mail::fake();

    $creator = User::factory()->create(['role' => UserRole::Ti]);
    $assignee = User::factory()->create(['role' => UserRole::Ti]);

    $response = $this->actingAs($creator)->post('/requests', [
        'title' => 'Servidor lento',
        'description' => 'O servidor está respondendo devagar.',
        'type' => 'rede',
        'assigned_to' => $assignee->id,
    ]);

    $response->assertRedirect(route('kanban'));
});

test('a regular user cannot assign a ticket to another regular user', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $otherUser = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)
        ->post('/requests', [
            'title' => 'Teste',
            'description' => 'Descrição de teste.',
            'type' => 'outro',
            'assigned_to' => $otherUser->id,
        ])
        ->assertSessionHasErrors('assigned_to');
});

test('ti can see all tickets on the kanban', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    Ticket::factory()->count(3)->create();

    $this->actingAs($ti)
        ->get('/kanban')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('tickets', 3));
});

test('a regular user cannot access the kanban', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->get('/kanban')->assertForbidden();
});

test('ti can change a ticket status and a history entry is recorded', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Aberta]);

    $this->actingAs($ti)
        ->patch("/tickets/{$ticket->id}/status", ['status' => 'em_andamento'])
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe(TicketStatus::EmAndamento);
    expect($ticket->statusHistories()->count())->toBe(1);
});

test('ti can edit a ticket', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $ticket = Ticket::factory()->create(['title' => 'Old title']);

    $this->actingAs($ti)
        ->put("/tickets/{$ticket->id}", [
            'title' => 'New title',
            'description' => $ticket->description,
            'type' => $ticket->type->value,
        ])
        ->assertRedirect();

    expect($ticket->fresh()->title)->toBe('New title');
});

test('ti can delete a ticket', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $ticket = Ticket::factory()->create();

    $this->actingAs($ti)
        ->delete("/tickets/{$ticket->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

test('a regular user cannot change ticket status or manage someone else\'s ticket', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $ticket = Ticket::factory()->create();

    $this->actingAs($user)->patch("/tickets/{$ticket->id}/status", ['status' => 'concluida'])
        ->assertForbidden();

    $this->actingAs($user)->put("/tickets/{$ticket->id}", [
        'title' => 'Hijacked',
        'description' => $ticket->description,
        'type' => $ticket->type->value,
    ])->assertForbidden();

    $this->actingAs($user)->delete("/tickets/{$ticket->id}")
        ->assertForbidden();
});

test('the owner can edit and delete their own ticket while it is still aberta', function () {
    $owner = User::factory()->create(['role' => UserRole::User]);
    $ticket = Ticket::factory()->create([
        'user_id' => $owner->id,
        'status' => TicketStatus::Aberta,
        'title' => 'Old title',
    ]);

    $this->actingAs($owner)
        ->put("/tickets/{$ticket->id}", [
            'title' => 'New title',
            'description' => $ticket->description,
            'type' => $ticket->type->value,
        ])
        ->assertRedirect();

    expect($ticket->fresh()->title)->toBe('New title');

    $this->actingAs($owner)
        ->delete("/tickets/{$ticket->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

test('the owner cannot edit or delete their ticket once it is no longer aberta', function () {
    $owner = User::factory()->create(['role' => UserRole::User]);
    $ticket = Ticket::factory()->create([
        'user_id' => $owner->id,
        'status' => TicketStatus::EmAndamento,
    ]);

    $this->actingAs($owner)
        ->put("/tickets/{$ticket->id}", [
            'title' => 'New title',
            'description' => $ticket->description,
            'type' => $ticket->type->value,
        ])
        ->assertForbidden();

    $this->actingAs($owner)
        ->delete("/tickets/{$ticket->id}")
        ->assertForbidden();
});

test('the requests index can be filtered by title, type, status and date range', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $match = Ticket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Impressora sem tinta',
        'type' => 'hardware',
        'status' => TicketStatus::Aberta,
        'created_at' => '2026-01-10',
    ]);
    Ticket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Acesso ao sistema',
        'type' => 'acesso',
        'status' => TicketStatus::Concluida,
        'created_at' => '2026-02-15',
    ]);

    $this->actingAs($user)
        ->get('/requests?'.http_build_query([
            'title' => 'Impressora',
            'type' => 'hardware',
            'status' => 'aberta',
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('tickets', 1)
            ->where('tickets.0.id', $match->id)
        );
});
