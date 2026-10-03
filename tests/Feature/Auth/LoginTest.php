<?php

use App\Enums\UserRole;
use App\Models\User;

test('a regular user can log in through the usuario portal', function () {
    $user = User::factory()->create(['role' => UserRole::User, 'password' => bcrypt('password')]);

    $response = $this->post('/login', [
        'type' => 'usuario',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('requests.index'));
    $this->assertAuthenticatedAs($user);
});

test('a ti user can log in through the ti portal', function () {
    $user = User::factory()->create(['role' => UserRole::Ti, 'password' => bcrypt('password')]);

    $response = $this->post('/login', [
        'type' => 'ti',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('a user cannot log in through the wrong portal', function () {
    $user = User::factory()->create(['role' => UserRole::User, 'password' => bcrypt('password')]);

    $response = $this->post('/login', [
        'type' => 'ti',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('a regular user cannot access ti-only routes', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->get('/dashboard')->assertForbidden();
    $this->actingAs($user)->get('/kanban')->assertForbidden();
});

test('a ti user cannot access user-only routes', function () {
    $user = User::factory()->create(['role' => UserRole::Ti]);

    $this->actingAs($user)->get('/requests')->assertForbidden();
    $this->actingAs($user)->get('/requests/create')->assertForbidden();
});

test('both roles can access settings', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $ti = User::factory()->create(['role' => UserRole::Ti]);

    $this->actingAs($user)->get('/settings')->assertOk();
    $this->actingAs($ti)->get('/settings')->assertOk();
});
