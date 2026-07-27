<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PopulationStatisticsRequest;
use App\Services\PopulationStatistics;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PopulationStatisticsController extends PopulationController
{
    public function __invoke(PopulationStatisticsRequest $request, PopulationStatistics $statistics): View
    {
        $validated = $request->validated();
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
