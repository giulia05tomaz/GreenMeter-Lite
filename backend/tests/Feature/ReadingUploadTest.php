<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('imports a valid csv for the authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $file = UploadedFile::fake()->createWithContent(
        'readings.csv',
        "timestamp,metric,value,unit\n2026-01-01T00:00:00Z,energy,10.5,kWh\n"
    );

    $this->postJson('/api/readings/upload', ['file' => $file])
        ->assertOk()
        ->assertJson(['inserted' => 1]);

    $this->assertDatabaseHas('readings', [
        'user_id' => $user->id,
        'metric' => 'energy',
        'value' => 10.5,
        'unit' => 'kWh',
        'source' => 'csv',
    ]);
});

it('rejects an unexpected header', function () {
    Sanctum::actingAs(User::factory()->create());
    $file = UploadedFile::fake()->createWithContent(
        'readings.csv',
        "date,type,amount,unit\n2026-01-01,energy,10,kWh\n"
    );

    $this->postJson('/api/readings/upload', ['file' => $file])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

it('rejects negative values without partially importing the file', function () {
    Sanctum::actingAs(User::factory()->create());
    $file = UploadedFile::fake()->createWithContent(
        'readings.csv',
        "timestamp,metric,value,unit\n2026-01-01T00:00:00Z,energy,10,kWh\n2026-01-02T00:00:00Z,energy,-1,kWh\n"
    );

    $this->postJson('/api/readings/upload', ['file' => $file])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    $this->assertDatabaseCount('readings', 0);
});
