<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\LetterApplication;
use App\Models\News;
use App\Models\SiteVisit;
use App\Models\StatisticDataset;
use App\Services\PopulationStatistics;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(PopulationStatistics $populationStatistics)
    {
        $populationGender = Schema::hasTable('residents')
            ? collect($populationStatistics->distribution('sex'))->map(fn (array $row) => ['label' => $row['label'], 'value' => $row['total']])->all()
            : [];
        $populationOccupation = Schema::hasTable('residents')
            ? collect($populationStatistics->distribution('occupation'))->map(fn (array $row) => ['label' => $row['label'], 'value' => $row['total']])->all()
            : [];

        $visitorCount = Schema::hasTable('site_visits') ? SiteVisit::count() : 0;
        $stats = [
            ['label' => 'Statistik Pengunjung', 'value' => $visitorCount, 'icon' => 'fa-line-chart', 'color' => 'bg-yellow'],
            ['label' => 'Berita', 'value' => News::count(), 'icon' => 'fa-newspaper-o', 'color' => 'bg-red'],
        ];
        if (Schema::hasTable('letter_applications') && auth()->user()->can('manage-letter-applications')) {
            $counts = LetterApplication::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
            $stats = array_merge($stats, [
                ['label' => 'Permohonan Baru', 'value' => $counts->get('submitted', 0), 'icon' => 'fa-envelope-o', 'color' => 'bg-aqua'],
                ['label' => 'Sedang Diverifikasi', 'value' => $counts->get('under_review', 0), 'icon' => 'fa-search', 'color' => 'bg-yellow'],
                ['label' => 'Sedang Diproses', 'value' => $counts->get('processing', 0), 'icon' => 'fa-cogs', 'color' => 'bg-blue'],
                ['label' => 'Siap Diambil', 'value' => $counts->get('ready_for_pickup', 0), 'icon' => 'fa-check', 'color' => 'bg-green'],
            ]);
        }

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
            'stats' => $stats,
            'messages' => ContactMessage::latest()->limit(5)->get(),
            'activities' => ActivityLog::with('user')
                ->whereDate('created_at', today())
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
