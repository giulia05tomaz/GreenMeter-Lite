<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reading;
use App\Models\EmissionFactor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Return KPI aggregates: total energy (kWh), total CO₂e (t) and daily average (kWh).
     */
    public function kpis(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Reading::query();
        if ($from) {
            $query->where('ts', '>=', Carbon::parse($from));
        }
        if ($to) {
            $query->where('ts', '<=', Carbon::parse($to));
        }

        $totalEnergyKwh = (float) $query->sum('value');

        $factor = EmissionFactor::where('metric', 'energy')->first();
        $totalCO2e = $factor ? $totalEnergyKwh * (float) $factor->factor : 0;

        $days = (int) $query->select(DB::raw('DATE(ts) as date'))->groupBy('date')->count();
        $dailyAvg = $days > 0 ? $totalEnergyKwh / $days : 0;

        return response()->json([
            'total_energy_kwh' => $totalEnergyKwh,
            'total_co2e_t' => $totalCO2e,
            'daily_avg_kwh' => $dailyAvg,
        ]);
    }

    /**
     * Return a daily series of kWh and CO₂e.
     */
    public function series(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Reading::query();
        if ($from) {
            $query->where('ts', '>=', Carbon::parse($from));
        }
        if ($to) {
            $query->where('ts', '<=', Carbon::parse($to));
        }

        $factor = EmissionFactor::where('metric', 'energy')->first();

        $series = $query
            ->select(DB::raw('DATE(ts) as date'), DB::raw('SUM(value) as kwh'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) use ($factor) {
                $kwh = (float) $item->kwh;
                return [
                    'date' => $item->date,
                    'kwh' => $kwh,
                    'co2e' => $factor ? $kwh * (float) $factor->factor : 0,
                ];
            });

        return response()->json($series);
    }
}