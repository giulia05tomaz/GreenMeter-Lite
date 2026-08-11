<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SplFileObject;
use Throwable;

class CsvReadingImporter
{
    private const EXPECTED_HEADER = ['timestamp', 'metric', 'value', 'unit'];

    private const MAX_ROWS = 10000;

    public function import(UploadedFile $file, User $user): int
    {
        $csv = new SplFileObject($file->getRealPath());
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $header = array_map(fn ($value) => strtolower(trim((string) $value)), $csv->fgetcsv());
        if ($header !== self::EXPECTED_HEADER) {
            throw ValidationException::withMessages([
                'file' => 'O cabeçalho deve ser: timestamp,metric,value,unit.',
            ]);
        }

        $records = [];
        $line = 1;

        while (! $csv->eof()) {
            $row = $csv->fgetcsv();
            $line++;
            if ($row === [null] || $row === false) {
                continue;
            }
            if (count($row) !== 4) {
                $this->invalidRow($line, 'a linha deve ter exatamente quatro colunas');
            }
            if (count($records) >= self::MAX_ROWS) {
                throw ValidationException::withMessages([
                    'file' => 'O arquivo excede o limite de 10.000 leituras.',
                ]);
            }

            [$timestamp, $metric, $value, $unit] = array_map(
                fn ($item) => trim((string) $item),
                $row
            );

            if ($metric !== 'energy' || $unit !== 'kWh') {
                $this->invalidRow($line, 'metric deve ser energy e unit deve ser kWh');
            }
            if (! is_numeric($value) || (float) $value < 0 || (float) $value > 999999999) {
                $this->invalidRow($line, 'value deve ser um número entre 0 e 999999999');
            }

            try {
                $parsedTimestamp = CarbonImmutable::parse($timestamp)->utc();
            } catch (Throwable) {
                $this->invalidRow($line, 'timestamp inválido');
            }

            $now = now();
            $records[] = [
                'user_id' => $user->id,
                'ts' => $parsedTimestamp,
                'metric' => $metric,
                'value' => round((float) $value, 3),
                'unit' => $unit,
                'source' => 'csv',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($records === []) {
            throw ValidationException::withMessages(['file' => 'O arquivo não contém leituras.']);
        }

        DB::transaction(function () use ($records): void {
            foreach (array_chunk($records, 500) as $chunk) {
                DB::table('readings')->insert($chunk);
            }
        });

        return count($records);
    }

    private function invalidRow(int $line, string $reason): never
    {
        throw ValidationException::withMessages([
            'file' => "Linha {$line}: {$reason}.",
        ]);
    }
}
