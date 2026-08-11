<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('rejects invalid credentials without exposing details', function () {
    User::factory()->create(['email' => 'test@example.com', 'password' => 'correct-password']);

    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ])->assertUnauthorized()->assertExactJson(['message' => 'Credenciais inválidas.']);
});

it('issues a sanctum token for valid credentials', function () {
    User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'correct-password',
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'correct-password',
    ])->assertOk()->assertJsonStructure([
        'token',
        'user' => ['name', 'email'],
    ]);

    expect(User::first()->tokens)->toHaveCount(1);
});

it('requires authentication for protected endpoints', function () {
    $this->getJson('/api/dashboard/kpis')->assertUnauthorized();
});

it('registers a user with a normalized email and a hashed password', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => '  Maria Silva  ',
        'email' => 'MARIA@EXEMPLO.COM',
        'password' => 'Senha123',
        'password_confirmation' => 'Senha123',
        'is_demo' => true,
    ])->assertCreated()->assertJsonPath('user.email', 'maria@exemplo.com')
        ->assertJsonPath('user.is_demo', false)
        ->assertJsonStructure(['token', 'user' => ['name', 'email', 'is_demo']]);

    $user = User::query()->where('email', 'maria@exemplo.com')->firstOrFail();
    expect($user->name)->toBe('Maria Silva')
        ->and($user->is_demo)->toBeFalse()
        ->and(Hash::check('Senha123', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('Senha123');
    expect($response->json('token'))->not->toBeEmpty();
});

it('rejects duplicate email and weak or unconfirmed passwords', function () {
    User::factory()->create(['email' => 'maria@exemplo.com']);

    $this->postJson('/api/auth/register', [
        'name' => 'Maria Silva',
        'email' => 'maria@exemplo.com',
        'password' => 'curta',
        'password_confirmation' => 'diferente',
    ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
});

it('issues a token for the read-only demonstration account', function () {
    User::factory()->create([
        'name' => 'Demonstração GreenMeter',
        'email' => 'demo@greenmeter.local',
        'is_demo' => true,
    ]);

    $this->postJson('/api/auth/demo')
        ->assertOk()
        ->assertJsonPath('user.is_demo', true)
        ->assertJsonStructure(['token', 'user' => ['name', 'email', 'is_demo']]);
});

it('does not create arbitrary users during login', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'unknown@example.com',
        'password' => 'anything',
    ])->assertUnauthorized();

    $this->assertDatabaseMissing('users', ['email' => 'unknown@example.com']);
});
