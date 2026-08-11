<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingUploadRequest;
use App\Models\ImportRecord;
use App\Services\CsvReadingImporter;
use Illuminate\Http\JsonResponse;

class ReadingUploadController extends Controller
{
    public function upload(ReadingUploadRequest $request, CsvReadingImporter $importer): JsonResponse
    {
        $result = $importer->import($request->file('file'), $request->user());
        $first = $result['first_reading_at'];
        $last = $result['last_reading_at'];

        ImportRecord::query()->create([
            'user_id' => $request->user()->id,
            'filename' => $request->file('file')->getClientOriginalName(),
            'line_count' => $result['inserted'],
            'first_reading_at' => $first,
            'last_reading_at' => $last,
            'status' => 'success',
            'message' => 'Importação concluída.',
        ]);

        return response()->json([
            'message' => 'Leituras importadas com sucesso.',
            'inserted' => $result['inserted'],
            'date_range' => $first->toDateString() === $last->toDateString()
                ? $first->toDateString()
                : $first->toDateString().' a '.$last->toDateString(),
        ]);
    }
}
