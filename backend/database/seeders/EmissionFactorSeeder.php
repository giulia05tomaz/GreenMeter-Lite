<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use Illuminate\Database\Seeder;

class EmissionFactorSeeder extends Seeder
{
    public function run(): void
    {
        EmissionFactor::query()->updateOrCreate(
            ['metric' => 'energy'],
            [
                'factor' => 0.000053,
                'unit_in' => 'kWh',
                'unit_out' => 'tCO2e',
            ]
        );
    }
}
