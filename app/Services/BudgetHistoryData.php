<?php

namespace App\Services;

use App\Models\Apbdes;
use Illuminate\Support\Facades\Schema;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

final class BudgetHistoryData
{
    public const FIRST_PUBLIC_YEAR = 2019;

    public const LATEST_PUBLIC_YEAR = 2025;

    private const JSON_RELATIVE_PATH = 'data/apbdes-sukomulyo-2019-2026.json';

    private ?array $payload = null;

    public function history(): array
    {
        if ($this->usesDatabase()) {
            return Apbdes::query()
                ->published()
                ->orderByDesc('year')
                ->get()
                ->map(fn (Apbdes $budget): array => $budget->historyPayload())
                ->all();
        }

        $history = $this->load()['history'] ?? [];

        if (! is_array($history)) {
            throw new UnexpectedValueException('Struktur history pada JSON APBDes tidak valid.');
        }

        $history = array_filter(
            $history,
            fn (mixed $item): bool => is_array($item)
                && is_numeric($item['year'] ?? null)
                && (int) $item['year'] >= self::FIRST_PUBLIC_YEAR
                && (int) $item['year'] <= self::LATEST_PUBLIC_YEAR,
        );

        usort(
            $history,
            fn (array $left, array $right): int => (int) $right['year'] <=> (int) $left['year'],
        );

        return array_values($history);
    }

    public function detail(int $year): ?array
    {
        if ($this->usesDatabase()) {
            return Apbdes::query()
                ->published()
                ->where('year', $year)
                ->first()
                ?->publicPayload();
        }

        if ($year < self::FIRST_PUBLIC_YEAR || $year > self::LATEST_PUBLIC_YEAR) {
            return null;
        }

        $years = $this->load()['years'] ?? [];

        if (! is_array($years)) {
            throw new UnexpectedValueException('Struktur years pada JSON APBDes tidak valid.');
        }

        $detail = $years[(string) $year] ?? null;

        return is_array($detail) ? $detail : null;
    }

    public function latest(): array
    {
        $latestYear = (int) (data_get($this->history(), '0.year') ?: self::LATEST_PUBLIC_YEAR);

        return $this->detail($latestYear)
            ?? throw new UnexpectedValueException(
                sprintf('Data APBDes tahun terbaru (%d) tidak tersedia.', $latestYear),
            );
    }

    private function usesDatabase(): bool
    {
        return Schema::hasTable('apbdes') && Apbdes::query()->exists();
    }

    private function load(): array
    {
        if ($this->payload !== null) {
            return $this->payload;
        }

        $path = public_path(self::JSON_RELATIVE_PATH);

        if (! is_file($path)) {
            throw new RuntimeException(sprintf('File data APBDes tidak ditemukan: %s', $path));
        }

        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            throw new RuntimeException('File data APBDes kosong atau tidak dapat dibaca.');
        }

        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'Format JSON APBDes tidak valid: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        if (
            ! is_array($payload)
            || ! isset($payload['history'], $payload['years'])
            || ! is_array($payload['history'])
            || ! is_array($payload['years'])
        ) {
            throw new UnexpectedValueException('JSON APBDes wajib memiliki struktur history dan years.');
        }

        return $this->payload = $payload;
    }
}
