<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('ti can list users', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    User::factory()->count(2)->create(['role' => UserRole::User]);

    $this->actingAs($ti)
        ->get('/usuarios')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('users', 3));
});

test('a regular user cannot list users', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->get('/usuarios')->assertForbidden();
});

test('ti can reset a user password', function () {
    $ti = User::factory()->create(['role' => UserRole::Ti]);
    $user = User::factory()->create(['role' => UserRole::User]);
    $originalPassword = $user->password;

    $this->actingAs($ti)
        ->post("/usuarios/{$user->id}/redefinir-senha")
        ->assertRedirect();

    expect($user->fresh()->password)->not->toBe($originalPassword);
});

test('a user can change their own password', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user)
        ->post('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect();

    $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
});

test('changing password fails with wrong current password', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user)
        ->post('/settings/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertSessionHasErrors('current_password');
});
