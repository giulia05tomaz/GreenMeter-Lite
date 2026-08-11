<?php

namespace App\Http\Controllers;

use App\Models\ImportRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $records = ImportRecord::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (ImportRecord $record) => [
                'id' => $record->id,
                'filename' => $record->filename,
                'line_count' => $record->line_count,
                'date_range' => $record->first_reading_at?->toDateString() === $record->last_reading_at?->toDateString()
                    ? $record->first_reading_at?->toDateString()
                    : $record->first_reading_at?->toDateString().' a '.$record->last_reading_at?->toDateString(),
                'status' => $record->status,
                'message' => $record->message,
                'created_at' => $record->created_at?->toISOString(),
            ]);

        return response()->json($records);
    }
}
