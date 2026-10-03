<?php

use App\Enums\AccessRequestStatus;
use App\Enums\UserRole;
use App\Mail\AccessRequestApproved;
use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('a visitor can submit an access request', function () {
    $response = $this->post('/solicitar-acesso', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'reason' => 'Preciso acompanhar minhas solicitações de TI.',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('access_requests', [
        'email' => 'jane@example.com',
        'status' => AccessRequestStatus::Pending->value,
    ]);
});

test('a visitor cannot submit a duplicate pending access request', function () {
    AccessRequest::factory()->create(['email' => 'jane@example.com']);

    $response = $this->post('/solicitar-acesso', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'reason' => 'Tentando de novo.',
    ]);

    $response->assertSessionHasErrors('email');
    expect(AccessRequest::where('email', 'jane@example.com')->count())->toBe(1);
});

test('ti can see pending access requests', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    AccessRequest::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($ti)
        ->get('/solicitacoes')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('requests', 1));
});

test('a regular user cannot see access requests', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->get('/solicitacoes')->assertForbidden();
});

test('ti accepting a request creates a user and emails the password', function () {
    Mail::fake();

    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $accessRequest = AccessRequest::factory()->create(['email' => 'jane@example.com']);

    $this->actingAs($ti)
        ->post("/solicitacoes/{$accessRequest->id}/aceitar")
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'role' => UserRole::User->value,
    ]);

    expect($accessRequest->fresh()->status)->toBe(AccessRequestStatus::Accepted);

    Mail::assertSent(AccessRequestApproved::class, fn ($mail) => $mail->hasTo('jane@example.com'));
});

test('ti declining a request does not create a user', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $accessRequest = AccessRequest::factory()->create(['email' => 'jane@example.com']);

    $this->actingAs($ti)
        ->post("/solicitacoes/{$accessRequest->id}/recusar")
        ->assertRedirect();

    $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    expect($accessRequest->fresh()->status)->toBe(AccessRequestStatus::Declined);
});

test('an already reviewed request cannot be accepted again', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $accessRequest = AccessRequest::factory()->create([
        'email' => 'jane@example.com',
        'status' => AccessRequestStatus::Declined,
    ]);

    $this->actingAs($ti)->post("/solicitacoes/{$accessRequest->id}/aceitar");

    $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
});
