<?php

namespace App\Services\Statistics;

use Illuminate\Support\Str;
use JsonException;

final class NormalizedStatisticsValidator
{
    public const SCHEMA_NAME = 'normalized_census_statistics';

    public const SCHEMA_VERSION = '2.0';

    private const REQUIRED_DATASET_FIELDS = [
        'dataset_id',
        'family',
        'table_number',
        'title',
        'period',
        'columns',
        'rows',
        'source',
    ];

    private const COLUMN_TYPES = ['string', 'integer', 'percentage'];

    public function __construct(private StatisticCategoryDetector $categoryDetector) {}

    /**
     * @return array{
     *     is_valid: bool,
     *     schema_name: ?string,
     *     schema_version: ?string,
     *     total_datasets: int,
     *     total_rows: int,
     *     warnings: array<int, array<string, mixed>>,
     *     errors: array<int, array<string, mixed>>,
     *     datasets: array<int, array<string, mixed>>,
     *     data: array<string, mixed>
     * }
     */
    public function validate(string $path): array
    {
        $errors = [];
        $warnings = [];
        $data = [];

        if (! is_file($path) || ! is_readable($path)) {
            $errors[] = $this->issue('file_unreadable', 'File JSON tidak ditemukan atau tidak dapat dibaca.');

            return $this->result($data, [], $warnings, $errors, null, null, 0);
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            $data = is_array($decoded) ? $decoded : [];
        } catch (JsonException $exception) {
            $errors[] = $this->issue('invalid_json', 'Isi file bukan JSON yang valid: '.$exception->getMessage());

            return $this->result($data, [], $warnings, $errors, null, null, 0);
        }

        $schemaName = data_get($data, 'metadata.schema.name');
        $schemaVersion = data_get($data, 'metadata.schema.version');

        if ($schemaName !== self::SCHEMA_NAME) {
            $errors[] = $this->issue(
                'invalid_schema_name',
                'Schema harus bernama '.self::SCHEMA_NAME.'.',
            );
        }

        if ((string) $schemaVersion !== self::SCHEMA_VERSION) {
            $errors[] = $this->issue(
                'invalid_schema_version',
                'Versi schema harus '.self::SCHEMA_VERSION.'.',
            );
        }

        foreach (['normalization_errors_detail', 'validation_errors_detail'] as $key) {
            foreach ($this->issueList(data_get($data, "metadata.{$key}", [])) as $issue) {
                $errors[] = $issue;
            }
        }

        $warnings = $this->issueList(data_get($data, 'metadata.validation_warnings_detail', []));
        $datasets = $data['datasets'] ?? null;

        if (! is_array($datasets)) {
            $errors[] = $this->issue('invalid_datasets', 'Properti datasets harus berupa array.');
            $datasets = [];
        }

        $seenDatasetIds = [];
        $preview = [];
        $totalRows = 0;
        $warningsByDataset = collect($warnings)->groupBy('dataset_id');

        foreach ($datasets as $datasetIndex => $dataset) {
            if (! is_array($dataset)) {
                $errors[] = $this->issue(
                    'invalid_dataset',
                    'Dataset pada indeks '.$datasetIndex.' harus berupa object.',
                );

                continue;
            }

            $datasetId = trim((string) ($dataset['dataset_id'] ?? ''));
            $missing = array_values(array_filter(
                self::REQUIRED_DATASET_FIELDS,
                fn (string $field): bool => ! array_key_exists($field, $dataset),
            ));

            if ($missing !== []) {
                $errors[] = $this->issue(
                    'missing_dataset_fields',
                    'Dataset kehilangan field wajib: '.implode(', ', $missing).'.',
                    $datasetId ?: null,
                );
            }

            if ($datasetId === '') {
                $errors[] = $this->issue(
                    'empty_dataset_id',
                    'dataset_id tidak boleh kosong.',
                );
            } elseif (isset($seenDatasetIds[$datasetId])) {
                $errors[] = $this->issue(
                    'duplicate_dataset_id',
                    "dataset_id {$datasetId} muncul lebih dari satu kali.",
                    $datasetId,
                );
            } else {
                $seenDatasetIds[$datasetId] = true;
            }

            $columns = $dataset['columns'] ?? [];
            $rows = $dataset['rows'] ?? [];
            $columnKeys = [];

            if (! is_array($columns) || $columns === []) {
                $errors[] = $this->issue(
                    'invalid_columns',
                    'columns harus berupa array yang tidak kosong.',
                    $datasetId ?: null,
                );
                $columns = [];
            }

            foreach ($columns as $columnIndex => $column) {
                if (! is_array($column)) {
                    $errors[] = $this->issue(
                        'invalid_column',
                        "Kolom indeks {$columnIndex} harus berupa object.",
                        $datasetId ?: null,
                    );

                    continue;
                }

                $key = trim((string) ($column['key'] ?? ''));
                $label = trim((string) ($column['label'] ?? ''));
                $type = (string) ($column['type'] ?? '');

                if ($key === '' || $label === '' || ! in_array($type, self::COLUMN_TYPES, true)) {
                    $errors[] = $this->issue(
                        'invalid_column_definition',
                        "Kolom indeks {$columnIndex} harus memiliki key, label, dan type yang didukung.",
                        $datasetId ?: null,
                    );
                }

                if ($key !== '' && isset($columnKeys[$key])) {
                    $errors[] = $this->issue(
                        'duplicate_column_key',
                        "Key kolom {$key} digunakan lebih dari satu kali.",
                        $datasetId ?: null,
                    );
                }

                if ($key !== '') {
                    $columnKeys[$key] = $type;
                }
            }

            if (! is_array($rows)) {
                $errors[] = $this->issue(
                    'invalid_rows',
                    'rows harus berupa array.',
                    $datasetId ?: null,
                );
                $rows = [];
            }

            foreach ($rows as $rowIndex => $row) {
                if (! is_array($row)) {
                    $errors[] = $this->issue(
                        'invalid_row',
                        "Baris indeks {$rowIndex} harus berupa object.",
                        $datasetId ?: null,
                    );

                    continue;
                }

                $missingKeys = array_values(array_diff(array_keys($columnKeys), array_keys($row)));
                if ($missingKeys !== []) {
                    $errors[] = $this->issue(
                        'missing_row_values',
                        "Baris indeks {$rowIndex} tidak memiliki nilai: ".implode(', ', $missingKeys).'.',
                        $datasetId ?: null,
                    );
                }
            }

            $rowCount = count($rows);
            $totalRows += $rowCount;
            $datasetWarnings = $warningsByDataset->get($datasetId, collect())->values()->all();
            $title = trim((string) ($dataset['title'] ?? 'Dataset tanpa judul'));

            $preview[] = [
                'dataset_id' => $datasetId,
                'family' => (string) ($dataset['family'] ?? ''),
                'table_number' => (string) ($dataset['table_number'] ?? ''),
                'period' => (string) ($dataset['period'] ?? ''),
                'title' => $title,
                'short_title' => Str::limit(Str::title(mb_strtolower($title)), 150, ''),
                'suggested_category' => $this->categoryDetector->detect(
                    $title,
                    (string) ($dataset['family'] ?? ''),
                ),
                'row_count' => $rowCount,
                'warnings' => $datasetWarnings,
                'requires_manual_review' => $datasetWarnings !== [],
            ];
        }

        $summaryDatasets = data_get($data, 'metadata.summary.normalized_datasets');
        $summaryRows = data_get($data, 'metadata.summary.normalized_data_rows');

        if ($summaryDatasets !== null && (int) $summaryDatasets !== count($datasets)) {
            $warnings[] = $this->issue(
                'dataset_summary_mismatch',
                'Jumlah dataset tidak sama dengan metadata.summary.normalized_datasets.',
            );
        }

        if ($summaryRows !== null && (int) $summaryRows !== $totalRows) {
            $warnings[] = $this->issue(
                'row_summary_mismatch',
                'Jumlah baris tidak sama dengan metadata.summary.normalized_data_rows.',
            );
        }

        return $this->result(
            $data,
            $preview,
            $warnings,
            $errors,
            is_scalar($schemaName) ? (string) $schemaName : null,
            is_scalar($schemaVersion) ? (string) $schemaVersion : null,
            $totalRows,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $datasets
     * @param  array<int, array<string, mixed>>  $warnings
     * @param  array<int, array<string, mixed>>  $errors
     * @return array<string, mixed>
     */
    private function result(
        array $data,
        array $datasets,
        array $warnings,
        array $errors,
        ?string $schemaName,
        ?string $schemaVersion,
        int $totalRows,
    ): array {
        return [
            'is_valid' => $errors === [],
            'schema_name' => $schemaName,
            'schema_version' => $schemaVersion,
            'total_datasets' => count($datasets),
            'total_rows' => $totalRows,
            'warnings' => $warnings,
            'errors' => $errors,
            'datasets' => $datasets,
            'data' => $data,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function issueList(mixed $issues): array
    {
        if (! is_array($issues)) {
            return $issues === null || $issues === ''
                ? []
                : [$this->issue('source_issue', (string) $issues)];
        }

        return collect($issues)
            ->map(function (mixed $issue): array {
                if (! is_array($issue)) {
                    return $this->issue('source_issue', (string) $issue);
                }

                return [
                    'dataset_id' => isset($issue['dataset_id']) ? (string) $issue['dataset_id'] : null,
                    'type' => (string) ($issue['type'] ?? 'source_issue'),
                    'message' => (string) ($issue['message'] ?? json_encode($issue, JSON_UNESCAPED_UNICODE)),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{dataset_id: ?string, type: string, message: string}
     */
    private function issue(string $type, string $message, ?string $datasetId = null): array
    {
        return [
            'dataset_id' => $datasetId,
            'type' => $type,
            'message' => $message,
        ];
    }
}
