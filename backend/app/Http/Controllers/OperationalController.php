<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class OperationalController extends Controller
{
    public function readiness(): JsonResponse
    {
        DB::select('select 1');

        return response()->json(['status' => 'ok', 'database' => 'connected']);
    }

    public function sample(): Response
    {
        $csv = "timestamp,metric,value,unit\n"
            ."2026-01-01T00:00:00Z,energy,12.5,kWh\n"
            ."2026-01-02T00:00:00Z,energy,16.2,kWh\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="energy_readings.csv"',
        ]);
    }

    public function docs(): BinaryFileResponse
    {
        $path = resource_path('openapi.yaml');
        abort_unless(File::exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/yaml; charset=UTF-8']);
    }
}
