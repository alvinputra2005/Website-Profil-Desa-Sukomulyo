<?php

namespace App\Http\Controllers\Admin;

use App\Services\PopulationStatistics;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PopulationStatisticsController extends PopulationController
{
    public function __invoke(Request $request, PopulationStatistics $statistics): View
    {
        $validated = validator($request->query(), [
            'category' => ['nullable', Rule::in(['sex', 'age', 'area', 'religion', 'education', 'occupation', 'marital_status', 'citizenship'])],
        ])->validate();
        $category = $validated['category'] ?? 'sex';
        $categories = [
            'sex' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'area' => 'Wilayah',
            'religion' => 'Agama',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital_status' => 'Status Perkawinan',
            'citizenship' => 'Kewarganegaraan',
        ];

        return view('admin.population.statistics', [
            'summary' => $statistics->summary(),
            'rows' => $statistics->distribution($category),
            'category' => $category,
            'categories' => $categories,
        ]);
    }
}
