<?php

namespace App\Support;

/**
 * Creates merged HTML-table header cells from normalized statistic column paths.
 */
final class StatisticTableHeader
{
    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, array<int, array{label: string, colspan: int, rowspan: int}>>
     */
    public static function make(array $columns): array
    {
        $root = ['children' => [], 'column' => null];
        $maxDepth = 1;

        foreach ($columns as $index => $column) {
            $path = array_values(array_filter(
                $column['header_path'] ?? [],
                static fn ($label): bool => filled($label),
            ));
            $path = array_map(static fn ($label): string => trim((string) $label), $path);

            if ($path === []) {
                $path = [trim((string) ($column['label'] ?? $column['key'] ?? 'Kolom'))];
            }

            $maxDepth = max($maxDepth, count($path));
            $node =& $root;

            foreach ($path as $depth => $label) {
                // A duplicate path is still represented by its own leaf column.
                $childKey = $label;
                if ($depth === count($path) - 1 && isset($node['children'][$childKey])) {
                    $childKey .= "\0".$index;
                }

                $node['children'][$childKey] ??= [
                    'label' => $label,
                    'children' => [],
                    'column' => null,
                ];
                $node =& $node['children'][$childKey];
            }

            $node['column'] = $column;
            unset($node);
        }

        $rows = array_fill(0, $maxDepth, []);
        foreach ($root['children'] as $node) {
            self::appendCells($node, 1, $maxDepth, $rows);
        }

        return $rows;
    }

    /**
     * @param  array{label?: string, children: array<string, mixed>, column: mixed}  $node
     * @param  array<int, array<int, array{label: string, colspan: int, rowspan: int}>>  $rows
     */
    private static function appendCells(array $node, int $depth, int $maxDepth, array &$rows): int
    {
        if ($node['children'] === []) {
            $rows[$depth - 1][] = [
                'label' => (string) $node['label'],
                'colspan' => 1,
                'rowspan' => $maxDepth - $depth + 1,
            ];

            return 1;
        }

        $colspan = 0;
        foreach ($node['children'] as $child) {
            $colspan += self::appendCells($child, $depth + 1, $maxDepth, $rows);
        }

        $rows[$depth - 1][] = [
            'label' => (string) $node['label'],
            'colspan' => $colspan,
            'rowspan' => 1,
        ];

        return $colspan;
    }
}
