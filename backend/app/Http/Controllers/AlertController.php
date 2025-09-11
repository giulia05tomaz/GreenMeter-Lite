<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reading;
use Illuminate\Support\Facades\DB;

class AlertController extends Controller
{
    /**
     * Return a list of dates where daily consumption exceeds 130 % of the weekly average.
     */
    public function index(Request $request)
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

        // Daily totals for the given range
        $daily = $query
            ->select(DB::raw('DATE(ts) as date'), DB::raw('SUM(value) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($daily->isEmpty()) {
            return response()->json([]);
        }

        // Determine a reference week to compute the average. Use the first day in range.
        $weekStart = Carbon::parse($from ?? $daily->first()->date)->startOfWeek();
        $weekEnd = Carbon::parse($from ?? $daily->first()->date)->endOfWeek();

        $weekData = Reading::query()
            ->whereBetween('ts', [$weekStart, $weekEnd])
            ->select(DB::raw('DATE(ts) as date'), DB::raw('SUM(value) as total'))
            ->groupBy('date')
            ->get();

        $weeklyAvg = $weekData->avg('total') ?: 0;
        $threshold = $weeklyAvg * 1.3;

        $peaks = $daily->filter(fn($item) => $item->total > $threshold)
            ->map(fn($item) => $item->date)
            ->values();

        return response()->json($peaks);
    }
}