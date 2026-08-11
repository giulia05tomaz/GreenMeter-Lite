<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingUploadRequest;
use App\Services\CsvReadingImporter;
use Illuminate\Http\JsonResponse;

class ReadingUploadController extends Controller
{
    public function upload(ReadingUploadRequest $request, CsvReadingImporter $importer): JsonResponse
    {
        $inserted = $importer->import($request->file('file'), $request->user());

        return response()->json([
            'message' => 'Leituras importadas com sucesso.',
            'inserted' => $inserted,
        ]);
    }
}
