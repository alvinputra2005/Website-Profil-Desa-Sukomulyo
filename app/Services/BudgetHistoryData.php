<?php

namespace App\Services;

final class BudgetHistoryData
{
    private const SUMMARIES = [
        ['year' => 2026, 'income' => 2485000000, 'spending' => 2350000000, 'realization' => 1739000000, 'percentage' => 74],
        ['year' => 2025, 'income' => 2360000000, 'spending' => 2240000000, 'realization' => 1859200000, 'percentage' => 83],
        ['year' => 2024, 'income' => 2225000000, 'spending' => 2100000000, 'realization' => 1743000000, 'percentage' => 83],
        ['year' => 2023, 'income' => 2080000000, 'spending' => 1980000000, 'realization' => 1623600000, 'percentage' => 82],
        ['year' => 2022, 'income' => 1950000000, 'spending' => 1860000000, 'realization' => 1488000000, 'percentage' => 80],
        ['year' => 2021, 'income' => 1820000000, 'spending' => 1740000000, 'realization' => 1357200000, 'percentage' => 78],
        ['year' => 2020, 'income' => 1690000000, 'spending' => 1610000000, 'realization' => 1207500000, 'percentage' => 75],
        ['year' => 2019, 'income' => 1560000000, 'spending' => 1480000000, 'realization' => 1213600000, 'percentage' => 82],
        ['year' => 2018, 'income' => 1420000000, 'spending' => 1360000000, 'realization' => 1074400000, 'percentage' => 79],
        ['year' => 2017, 'income' => 1300000000, 'spending' => 1240000000, 'realization' => 954800000, 'percentage' => 77],
    ];

    private const SPENDING_CATEGORIES = [
        [
            'code' => '2.1',
            'name' => 'Penyelenggaraan Pemerintahan Desa',
            'short_name' => 'Pemerintahan',
            'description' => 'Operasional pemerintahan, tata kelola, pelayanan administrasi, dan penghasilan aparatur desa.',
            'programs' => [
                ['name' => 'Penghasilan tetap dan tunjangan aparatur', 'weight' => 45],
                ['name' => 'Operasional kantor dan pelayanan administrasi', 'weight' => 35],
                ['name' => 'Pendataan, perencanaan, dan pelaporan desa', 'weight' => 20],
            ],
        ],
        [
            'code' => '2.2',
            'name' => 'Pelaksanaan Pembangunan Desa',
            'short_name' => 'Pembangunan',
            'description' => 'Infrastruktur dasar, permukiman, kesehatan, pendidikan, serta sarana publik desa.',
            'programs' => [
                ['name' => 'Jalan lingkungan, drainase, dan jembatan desa', 'weight' => 42],
                ['name' => 'Air bersih, sanitasi, dan lingkungan permukiman', 'weight' => 22],
                ['name' => 'Pelayanan kesehatan desa dan Posyandu', 'weight' => 20],
                ['name' => 'Sarana pendidikan dan literasi masyarakat', 'weight' => 16],
            ],
        ],
        [
            'code' => '2.3',
            'name' => 'Pembinaan Kemasyarakatan',
            'short_name' => 'Pembinaan',
            'description' => 'Ketenteraman, kelembagaan masyarakat, kepemudaan, olahraga, seni, dan budaya.',
            'programs' => [
                ['name' => 'Ketenteraman, perlindungan, dan keamanan lingkungan', 'weight' => 35],
                ['name' => 'Pembinaan lembaga kemasyarakatan desa', 'weight' => 34],
                ['name' => 'Kepemudaan, olahraga, seni, dan budaya', 'weight' => 31],
            ],
        ],
        [
            'code' => '2.4',
            'name' => 'Pemberdayaan Masyarakat',
            'short_name' => 'Pemberdayaan',
            'description' => 'Penguatan ekonomi warga, BUM Desa, pertanian, ketahanan pangan, dan peningkatan kapasitas.',
            'programs' => [
                ['name' => 'Pengembangan UMKM dan BUM Desa', 'weight' => 36],
                ['name' => 'Pertanian dan ketahanan pangan desa', 'weight' => 34],
                ['name' => 'Pelatihan keterampilan dan literasi digital', 'weight' => 30],
            ],
        ],
        [
            'code' => '2.5',
            'name' => 'Penanggulangan Bencana, Darurat, dan Mendesak',
            'short_name' => 'Darurat',
            'description' => 'Kesiapsiagaan bencana, keadaan darurat, dan bantuan langsung untuk kebutuhan mendesak.',
            'programs' => [
                ['name' => 'Kesiapsiagaan dan penanggulangan bencana', 'weight' => 40],
                ['name' => 'Bantuan langsung dan keadaan mendesak desa', 'weight' => 60],
            ],
        ],
    ];

    private const REVENUE_CATEGORIES = [
        ['code' => '1.1', 'name' => 'Pendapatan Asli Desa', 'short_name' => 'PADes'],
        ['code' => '1.2.1', 'name' => 'Dana Desa', 'short_name' => 'Dana Desa'],
        ['code' => '1.2.2', 'name' => 'Alokasi Dana Desa', 'short_name' => 'ADD'],
        ['code' => '1.2.3', 'name' => 'Bagi Hasil Pajak dan Retribusi', 'short_name' => 'Bagi Hasil'],
        ['code' => '1.2.4', 'name' => 'Bantuan Keuangan Provinsi', 'short_name' => 'Bantuan Provinsi'],
        ['code' => '1.2.5', 'name' => 'Bantuan Keuangan Kabupaten', 'short_name' => 'Bantuan Kabupaten'],
        ['code' => '1.3', 'name' => 'Pendapatan Lain-lain yang Sah', 'short_name' => 'Lain-lain'],
    ];

    public function history(): array
    {
        return array_map(
            fn (array $summary): array => $summary + [
                'remaining_budget' => $summary['spending'] - $summary['realization'],
                'balance' => $summary['income'] - $summary['spending'],
            ],
            self::SUMMARIES,
        );
    }

    public function detail(int $year): ?array
    {
        $summary = collect(self::SUMMARIES)->firstWhere('year', $year);

        if (! $summary) {
            return null;
        }

        $cycle = ($year - 2017) % 4;
        $spendingWeights = [18 + ($cycle * .4), 42 - ($cycle * .3), 11, 18, 11 - ($cycle * .1)];
        $spendingBudgets = $this->allocate($summary['spending'], $spendingWeights);
        $absorptionFactors = [0.86, 0.91, 0.78, 0.84, 0.73];
        $realizationWeights = array_map(
            fn (int $budget, int $index): float => $budget * ($absorptionFactors[$index] + ($cycle * .008)),
            $spendingBudgets,
            array_keys($spendingBudgets),
        );
        $spendingRealizations = $this->allocate($summary['realization'], $realizationWeights);

        $spending = [];
        $programs = [];
        foreach (self::SPENDING_CATEGORIES as $index => $category) {
            $budget = $spendingBudgets[$index];
            $realization = $spendingRealizations[$index];
            $programBudgets = $this->allocate($budget, array_column($category['programs'], 'weight'));
            $programRealizations = $this->allocate($realization, $programBudgets);
            $categoryPrograms = [];

            foreach ($category['programs'] as $programIndex => $program) {
                $programRow = [
                    'code' => $category['code'].'.'.($programIndex + 1),
                    'category' => $category['name'],
                    'name' => $program['name'],
                    'budget' => $programBudgets[$programIndex],
                    'realization' => $programRealizations[$programIndex],
                    'remaining' => $programBudgets[$programIndex] - $programRealizations[$programIndex],
                    'percentage' => $this->percentage($programRealizations[$programIndex], $programBudgets[$programIndex]),
                ];
                $categoryPrograms[] = $programRow;
                $programs[] = $programRow;
            }

            $spending[] = [
                'code' => $category['code'],
                'name' => $category['name'],
                'short_name' => $category['short_name'],
                'description' => $category['description'],
                'budget' => $budget,
                'realization' => $realization,
                'remaining' => $budget - $realization,
                'percentage' => $this->percentage($realization, $budget),
                'programs' => $categoryPrograms,
            ];
        }

        $revenueWeights = [8 + ($cycle * .3), 42 - ($cycle * .2), 25, 5, 6, 10, 4 - ($cycle * .1)];
        $revenueBudgets = $this->allocate($summary['income'], $revenueWeights);
        $revenueRealizationTotal = (int) round($summary['income'] * (.965 + ($cycle * .006)));
        $revenueFactors = [1.02, .99, .985, .95, .97, .96, .92];
        $revenueRealizationWeights = array_map(
            fn (int $budget, int $index): float => $budget * $revenueFactors[$index],
            $revenueBudgets,
            array_keys($revenueBudgets),
        );
        $revenueRealizations = $this->allocate($revenueRealizationTotal, $revenueRealizationWeights);
        $revenue = array_map(
            fn (array $category, int $index): array => $category + [
                'budget' => $revenueBudgets[$index],
                'realization' => $revenueRealizations[$index],
                'variance' => $revenueRealizations[$index] - $revenueBudgets[$index],
                'percentage' => $this->percentage($revenueRealizations[$index], $revenueBudgets[$index]),
            ],
            self::REVENUE_CATEGORIES,
            array_keys(self::REVENUE_CATEGORIES),
        );

        $financingReceipt = (int) round($summary['spending'] * (.028 + ($cycle * .002)));
        $financingExpenditure = (int) round($summary['spending'] * (.012 + ($cycle * .001)));
        $financing = [
            ['code' => '3.1.1', 'name' => 'SILPA Tahun Sebelumnya', 'type' => 'Penerimaan', 'amount' => $financingReceipt],
            ['code' => '3.2.1', 'name' => 'Penyertaan Modal BUM Desa', 'type' => 'Pengeluaran', 'amount' => $financingExpenditure],
        ];

        $quarterPercentages = [18 + $cycle, 43 + $cycle, 71 + $cycle, 100];
        $quarters = array_map(
            fn (int $percentage, int $index): array => [
                'quarter' => 'Triwulan '.($index + 1),
                'percentage' => $percentage,
                'cumulative' => $index === 3
                    ? $summary['realization']
                    : (int) round($summary['realization'] * $percentage / 100),
            ],
            $quarterPercentages,
            array_keys($quarterPercentages),
        );
        foreach ($quarters as $index => &$quarter) {
            $quarter['period_realization'] = $quarter['cumulative'] - ($quarters[$index - 1]['cumulative'] ?? 0);
        }
        unset($quarter);

        $topAllocation = collect($spending)->sortByDesc('budget')->first();
        $bestAbsorption = collect($spending)->sortByDesc('percentage')->first();
        $lowestAbsorption = collect($spending)->sortBy('percentage')->first();

        return [
            'summary' => $summary + [
                'remaining_budget' => $summary['spending'] - $summary['realization'],
                'budget_balance' => $summary['income'] - $summary['spending'],
                'income_realization' => $revenueRealizationTotal,
                'income_percentage' => $this->percentage($revenueRealizationTotal, $summary['income']),
            ],
            'revenue' => $revenue,
            'spending' => $spending,
            'programs' => $programs,
            'financing' => $financing,
            'financing_summary' => [
                'receipt' => $financingReceipt,
                'expenditure' => $financingExpenditure,
                'net' => $financingReceipt - $financingExpenditure,
            ],
            'quarters' => $quarters,
            'insights' => [
                'top_allocation' => $topAllocation,
                'best_absorption' => $bestAbsorption,
                'lowest_absorption' => $lowestAbsorption,
            ],
        ];
    }

    private function allocate(int $total, array $weights): array
    {
        $weightTotal = array_sum($weights);
        $remaining = $total;
        $allocation = [];
        $lastIndex = array_key_last($weights);

        foreach ($weights as $index => $weight) {
            $value = $index === $lastIndex
                ? $remaining
                : (int) floor($total * $weight / $weightTotal);
            $allocation[] = $value;
            $remaining -= $value;
        }

        return $allocation;
    }

    private function percentage(int $value, int $total): float
    {
        return $total > 0 ? round($value / $total * 100, 2) : 0;
    }
}
