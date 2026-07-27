<?php

namespace App\Queries;

use App\Models\Official;

final class OfficialOrganizationQuery
{
    /**
     * Return active officials as an ordered, cycle-safe hierarchy.
     *
     * @return array<int, array{official: Official, children: array}>
     */
    public function tree(): array
    {
        $officials = Official::query()
            ->with('photo')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $byParent = $officials->groupBy(fn (Official $official) => (int) ($official->superior_id ?? 0));
        $visited = [];

        $build = function (int $parentId) use (&$build, &$visited, $byParent): array {
            return collect($byParent->get($parentId, collect()))
                ->reject(fn (Official $official) => isset($visited[$official->id]))
                ->map(function (Official $official) use (&$build, &$visited): array {
                    $visited[$official->id] = true;

                    return [
                        'official' => $official,
                        'children' => $build($official->id),
                    ];
                })
                ->all();
        };

        $nodes = $build(0);

        // Preserve data visibility if a superior has been deactivated or a
        // legacy record forms a cycle: render it as a top-level branch.
        foreach ($officials as $official) {
            if (isset($visited[$official->id])) {
                continue;
            }

            $visited[$official->id] = true;
            $nodes[] = [
                'official' => $official,
                'children' => $build($official->id),
            ];
        }

        return $nodes;
    }
}
