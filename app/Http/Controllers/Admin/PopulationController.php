<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopulationArea;

abstract class PopulationController extends Controller
{
    protected function resolveArea(array $data): ?PopulationArea
    {
        $hamlet = trim((string) ($data['hamlet'] ?? ''));
        if ($hamlet === '') {
            return null;
        }

        return PopulationArea::firstOrCreate([
            'hamlet' => $hamlet,
            'rw' => str_pad(trim((string) ($data['rw'] ?? '')), 2, '0', STR_PAD_LEFT),
            'rt' => str_pad(trim((string) ($data['rt'] ?? '')), 2, '0', STR_PAD_LEFT),
        ]);
    }

    protected function areaRules(): array
    {
        return [
            'hamlet' => ['nullable', 'string', 'max:100', 'required_with:rw,rt'],
            'rw' => ['nullable', 'digits_between:1,3'],
            'rt' => ['nullable', 'digits_between:1,3'],
        ];
    }
}
