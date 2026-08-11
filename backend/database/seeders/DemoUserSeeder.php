<?php

namespace Database\Seeders;

use App\Models\ImportRecord;
use App\Models\Reading;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'demo@greenmeter.local'],
            [
                'name' => 'Demonstração GreenMeter',
                'password' => Hash::make(Str::random(64)),
                'is_demo' => true,
            ]
        );

        $daily = [
            '2026-07-01' => 118.4,
            '2026-07-02' => 121.8,
            '2026-07-03' => 127.1,
            '2026-07-04' => 119.7,
            '2026-07-05' => 176.3,
            '2026-07-06' => 123.9,
            '2026-07-07' => 125.2,
            '2026-07-08' => 132.6,
            '2026-07-09' => 128.4,
            '2026-07-10' => 137.1,
            '2026-07-11' => 129.5,
            '2026-07-12' => 188.2,
            '2026-07-13' => 134.7,
            '2026-07-14' => 131.3,
        ];

        foreach ($daily as $date => $value) {
            Reading::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'ts' => CarbonImmutable::parse($date)->startOfDay(),
                    'metric' => 'energy',
                    'source' => 'demo',
                ],
                ['value' => $value, 'unit' => 'kWh']
            );
        }

        ImportRecord::query()->updateOrCreate(
            ['user_id' => $user->id, 'filename' => 'demo-energy-readings.csv'],
            [
                'line_count' => count($daily),
                'first_reading_at' => CarbonImmutable::parse(array_key_first($daily))->startOfDay(),
                'last_reading_at' => CarbonImmutable::parse(array_key_last($daily))->startOfDay(),
                'status' => 'success',
                'message' => 'Dados fictícios carregados para demonstração.',
            ]
        );
    }
}
