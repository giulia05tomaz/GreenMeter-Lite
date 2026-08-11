<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardFilterRequest;
use App\Models\EmissionFactor;
use App\Models\Reading;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function kpis(DashboardFilterRequest $request): JsonResponse
    {
        $query = $this->readings($request);
        $totalEnergyKwh = (float) (clone $query)->sum('value');
        $days = (int) (clone $query)
            ->selectRaw('COUNT(DISTINCT DATE(ts)) as aggregate')
            ->value('aggregate');
        $factor = (float) (EmissionFactor::query()->where('metric', 'energy')->value('factor') ?? 0);

        return response()->json([
            'total_energy_kwh' => round($totalEnergyKwh, 3),
            'total_co2e_t' => round($totalEnergyKwh * $factor, 8),
            'daily_avg_kwh' => $days > 0 ? round($totalEnergyKwh / $days, 3) : 0,
        ]);
    }

    public function series(DashboardFilterRequest $request): JsonResponse
    {
        $factor = (float) (EmissionFactor::query()->where('metric', 'energy')->value('factor') ?? 0);
        $series = $this->readings($request)
            ->select(DB::raw('DATE(ts) as date'), DB::raw('SUM(value) as kwh'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'kwh' => round((float) $item->kwh, 3),
                'co2e' => round((float) $item->kwh * $factor, 8),
            ]);

        return response()->json($series);
    }

    private function readings(DashboardFilterRequest $request): Builder
    {
        $filters = $request->validated();
        $query = Reading::query()
            ->where('user_id', $request->user()->id)
            ->where('metric', 'energy')
            ->where('unit', 'kWh');

        if (isset($filters['from'])) {
            $query->where('ts', '>=', CarbonImmutable::parse($filters['from'])->startOfDay());
        }
        if (isset($filters['to'])) {
            $query->where('ts', '<=', CarbonImmutable::parse($filters['to'])->endOfDay());
        }

        return $query;
    }
}
