<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReadingUploadController extends Controller
{
    /**
     * Upload and insert readings from a CSV file.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        // Normalize header to lowercase trimmed values
        $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);
        if ($header !== ['timestamp', 'metric', 'value', 'unit']) {
            return response()->json(['message' => 'Invalid CSV headers'], 422);
        }

        $records = [];
        foreach (array_slice($rows, 1) as $row) {
            if (count($row) < 4) {
                continue;
            }
            [$timestamp, $metric, $value, $unit] = $row;
            $records[] = [
                'ts' => Carbon::parse($timestamp),
                'metric' => $metric,
                'value' => (float) $value,
                'unit' => $unit,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            DB::table('readings')->insert($records);
        }

        return response()->json(['message' => 'Upload successful']);
    }
}