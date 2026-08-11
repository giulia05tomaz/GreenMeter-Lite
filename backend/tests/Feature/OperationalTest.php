<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reports readiness and adds a request id', function () {
    $this->getJson('/api/health/readiness')
        ->assertOk()
        ->assertJson(['status' => 'ok', 'database' => 'connected'])
        ->assertHeader('X-Request-ID');
});

it('replaces an unsafe request id', function () {
    $response = $this->withHeader('X-Request-ID', 'unsafe request id')
        ->getJson('/api/health/readiness')
        ->assertOk();

    expect($response->headers->get('X-Request-ID'))->not->toBe('unsafe request id');
});

it('serves the sample csv', function () {
    $this->get('/api/samples/energy_readings.csv')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertSee('timestamp,metric,value,unit');
});
