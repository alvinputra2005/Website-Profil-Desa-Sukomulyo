<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\News;
use App\Models\Setting;
use App\Models\StatisticDataset;
use App\Services\PopulationStatistics;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(PopulationStatistics $populationStatistics)
    {
        $populationSummary = Schema::hasTable('residents')
            ? $populationStatistics->summary()
            : ['residents' => 0, 'families' => 0, 'male' => 0, 'female' => 0];
        $populationGender = Schema::hasTable('residents')
            ? collect($populationStatistics->distribution('sex'))->map(fn (array $row) => ['label' => $row['label'], 'value' => $row['total']])->all()
            : [];
        $populationOccupation = Schema::hasTable('residents')
            ? collect($populationStatistics->distribution('occupation'))->map(fn (array $row) => ['label' => $row['label'], 'value' => $row['total']])->all()
            : [];

        $visitorCount = (int) (Setting::where('key', 'analytics.visitors')->value('value') ?? 0);

        $datasetValues = function (array $slugs, array $fallback): array {
            $dataset = StatisticDataset::with('values')
                ->whereIn('slug', $slugs)
                ->latest('year')
                ->first();

            if (! $dataset || $dataset->values->isEmpty()) {
                return $fallback;
            }

            return $dataset->values
                ->map(fn ($item) => ['label' => $item->label, 'value' => (float) $item->value])
                ->values()
                ->all();
        };

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Jumlah Penduduk', 'value' => $populationSummary['residents'], 'icon' => 'fa-users', 'color' => 'bg-aqua'],
                ['label' => 'Jumlah KK', 'value' => $populationSummary['families'], 'icon' => 'fa-home', 'color' => 'bg-green'],
                ['label' => 'Statistik Pengunjung', 'value' => $visitorCount, 'icon' => 'fa-line-chart', 'color' => 'bg-yellow'],
                ['label' => 'Berita', 'value' => News::count(), 'icon' => 'fa-newspaper-o', 'color' => 'bg-red'],
            ],
            'messages' => ContactMessage::latest()->limit(5)->get(),
            'activities' => ActivityLog::with('user')
                ->latest('created_at')
                ->paginate(3, ['*'], 'activities_page')
                ->withQueryString(),
            'statisticPanels' => [
                [
                    'title' => 'Statistik Penduduk',
                    'icon' => 'fa-users',
                    'unit' => 'jiwa',
                    'items' => $populationGender ?: $datasetValues(
                        ['penduduk-berdasarkan-jenis-kelamin', 'komposisi-penduduk'],
                        [['label' => 'Laki-laki', 'value' => 0], ['label' => 'Perempuan', 'value' => 0]],
                    ),
                ],
                [
                    'title' => 'Statistik Mata Pencaharian',
                    'icon' => 'fa-briefcase',
                    'unit' => 'orang',
                    'items' => $populationOccupation ?: $datasetValues(
                        ['mata-pencaharian', 'statistik-mata-pencaharian'],
                        [['label' => 'Belum terdata', 'value' => 0]],
                    ),
                ],
                [
                    'title' => 'Statistik Tenaga Kerja',
                    'icon' => 'fa-line-chart',
                    'unit' => 'orang',
                    'items' => $datasetValues(
                        ['tenaga-kerja', 'statistik-tenaga-kerja'],
                        [['label' => 'Bekerja', 'value' => 1320], ['label' => 'Belum/Tidak Bekerja', 'value' => 410], ['label' => 'Pelajar/Mahasiswa', 'value' => 420]],
                    ),
                ],
            ],
        ]);
    }
}
