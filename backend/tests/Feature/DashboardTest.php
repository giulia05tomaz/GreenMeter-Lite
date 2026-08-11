<?php

use App\Models\EmissionFactor;
use App\Models\Reading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns kpis and series only for the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    EmissionFactor::query()->create([
        'metric' => 'energy',
        'factor' => 0.000053,
        'unit_in' => 'kWh',
        'unit_out' => 'tCO2e',
    ]);
    Reading::query()->create([
        'user_id' => $user->id,
        'ts' => '2026-01-01 12:00:00',
        'metric' => 'energy',
        'value' => 10,
        'unit' => 'kWh',
    ]);
    Reading::query()->create([
        'user_id' => $user->id,
        'ts' => '2026-01-02 12:00:00',
        'metric' => 'energy',
        'value' => 20,
        'unit' => 'kWh',
    ]);
    Reading::query()->create([
        'user_id' => $other->id,
        'ts' => '2026-01-01 12:00:00',
        'metric' => 'energy',
        'value' => 999,
        'unit' => 'kWh',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/dashboard/kpis?from=2026-01-01&to=2026-01-02')
        ->assertOk()
        ->assertJson([
            'total_energy_kwh' => 30,
            'total_co2e_t' => 0.00159,
            'daily_avg_kwh' => 15,
        ]);

    $this->getJson('/api/dashboard/series')->assertOk()->assertJsonCount(2);
});

it('validates the date range', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/dashboard/kpis?from=2026-02-01&to=2026-01-01')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('to');
});
