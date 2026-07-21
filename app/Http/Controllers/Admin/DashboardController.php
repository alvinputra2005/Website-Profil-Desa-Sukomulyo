<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, ContactMessage, News, Setting, StatisticDataset};

class DashboardController extends Controller
{
    public function __invoke()
    {
        $statisticValue = function (array $slugs, int $fallback = 0): int {
            $dataset = StatisticDataset::with('values')
                ->whereIn('slug', $slugs)
                ->latest('year')
                ->first();

            return $dataset && $dataset->values->isNotEmpty()
                ? (int) round($dataset->values->sum('value'))
                : $fallback;
        };

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
                ['label' => 'Jumlah Penduduk', 'value' => $statisticValue(['jumlah-penduduk'], 2150), 'icon' => 'fa-users', 'color' => 'bg-aqua'],
                ['label' => 'Jumlah KK', 'value' => $statisticValue(['jumlah-kk', 'jumlah-kepala-keluarga'], 720), 'icon' => 'fa-home', 'color' => 'bg-green'],
                ['label' => 'Statistik Pengunjung', 'value' => $visitorCount, 'icon' => 'fa-line-chart', 'color' => 'bg-yellow'],
                ['label' => 'Berita', 'value' => News::count(), 'icon' => 'fa-newspaper-o', 'color' => 'bg-red'],
            ],
            'messages' => ContactMessage::latest()->limit(5)->get(),
            'activities' => ActivityLog::with('user')->latest('created_at')->limit(8)->get(),
            'statisticPanels' => [
                [
                    'title' => 'Statistik Penduduk',
                    'icon' => 'fa-users',
                    'unit' => 'jiwa',
                    'items' => $datasetValues(
                        ['penduduk-berdasarkan-jenis-kelamin', 'komposisi-penduduk'],
                        [['label' => 'Laki-laki', 'value' => 1085], ['label' => 'Perempuan', 'value' => 1065]],
                    ),
                ],
                [
                    'title' => 'Statistik Mata Pencaharian',
                    'icon' => 'fa-briefcase',
                    'unit' => 'orang',
                    'items' => $datasetValues(
                        ['mata-pencaharian', 'statistik-mata-pencaharian'],
                        [['label' => 'Pertanian & Perkebunan', 'value' => 1032], ['label' => 'Perdagangan & UMKM', 'value' => 516], ['label' => 'Jasa & Pegawai', 'value' => 387], ['label' => 'Lainnya', 'value' => 215]],
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
