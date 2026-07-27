<?php

namespace App\Queries\Officials;

use App\Models\Official;
use Illuminate\Support\Collection;

class OfficialOrganizationQuery
{
    public function tree(): array
    {
        $officials = Official::with('photo')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return $this->buildTree($officials);
    }

    private function buildTree(Collection $officials): array
    {
        $byParent = $officials->groupBy(fn (Official $official) => (int) ($official->superior_id ?? 0));
        $visited = [];
        $build = function (int $parentId) use (&$build, &$visited, $byParent): array {
            return collect($byParent->get($parentId, collect()))
                ->reject(fn (Official $official) => isset($visited[$official->id]))
                ->map(function (Official $official) use (&$build, &$visited): array {
                    $visited[$official->id] = true;

                    return ['official' => $official, 'children' => $build($official->id)];
                })->all();
        };

        $nodes = $build(0);
        foreach ($officials as $official) {
            if (! isset($visited[$official->id])) {
                $visited[$official->id] = true;
                $nodes[] = ['official' => $official, 'children' => $build($official->id)];
            }
        }

        return $nodes;
    }
}
