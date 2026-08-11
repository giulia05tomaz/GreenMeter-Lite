<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
