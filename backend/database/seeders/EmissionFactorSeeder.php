<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmissionFactor;

class EmissionFactorSeeder extends Seeder
{
    public function run(): void
    {
        EmissionFactor::updateOrCreate(
            ['metric' => 'energy'],
            [
                'factor' => 0.000053,
                'unit_in' => 'kWh',
                'unit_out' => 'tCO2e',
            ]
        );
    }
}