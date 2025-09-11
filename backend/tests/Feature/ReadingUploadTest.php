<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\EmissionFactor;

uses(RefreshDatabase::class);

it('uploads a valid CSV', function () {
    // Create user and emission factor
    $user = User::factory()->create();
    EmissionFactor::create([
        'metric' => 'energy',
        'factor' => 0.000053,
        'unit_in' => 'kWh',
        'unit_out' => 'tCO2e',
    ]);

    Sanctum::actingAs($user);

    $content = "timestamp,metric,value,unit\n2025-01-01T00:00:00Z,energy,10,kWh\n";
    $file = UploadedFile::fake()->createWithContent('readings.csv', $content);

    $response = $this->postJson('/api/readings/upload', [
        'file' => $file,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Upload successful']);

    $this->assertDatabaseCount('readings', 1);
});