<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardFilterRequest;
use App\Models\Reading;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AlertController extends Controller
{
    public function index(DashboardFilterRequest $request): JsonResponse
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

        $daily = $query
            ->select(DB::raw('DATE(ts) as date'), DB::raw('SUM(value) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $alerts = $daily
            ->groupBy(fn ($item) => CarbonImmutable::parse($item->date)->format('o-W'))
            ->flatMap(function ($week) {
                $threshold = (float) $week->avg('total') * 1.3;

                return $week
                    ->filter(fn ($item) => (float) $item->total > $threshold)
                    ->map(fn ($item) => [
                        'date' => $item->date,
                        'total_kwh' => round((float) $item->total, 3),
                        'threshold_kwh' => round($threshold, 3),
                    ]);
            })
            ->values();

        return response()->json($alerts);
    }
}
