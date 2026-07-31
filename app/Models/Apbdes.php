<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apbdes extends CmsModel
{
    protected $table = 'apbdes';

    protected $fillable = [
        'year',
        'title',
        'description',
        'status',
        'is_partial_year',
        'income_budget',
        'income_realization',
        'spending_budget',
        'spending_realization',
        'financing_receipt',
        'financing_expenditure',
        'revenue',
        'spending',
        'programs',
        'problems',
        'solutions',
        'programs_note',
        'quarters_note',
        'data_quality_notes',
        'source_document',
        'source_reference',
        'published_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_partial_year' => 'boolean',
            'income_budget' => 'decimal:2',
            'income_realization' => 'decimal:2',
            'spending_budget' => 'decimal:2',
            'spending_realization' => 'decimal:2',
            'financing_receipt' => 'decimal:2',
            'financing_expenditure' => 'decimal:2',
            'revenue' => 'array',
            'spending' => 'array',
            'programs' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function historyPayload(): array
    {
        $income = $this->number($this->income_budget);
        $spending = $this->number($this->spending_budget);
        $realization = $this->number($this->spending_realization);

        return [
            'year' => $this->year,
            'income' => $income,
            'income_realization' => $this->number($this->income_realization),
            'spending' => $spending,
            'realization' => $realization,
            'percentage' => $this->percentage($realization, $spending),
            'remaining_budget' => $spending - $realization,
            'balance' => $income - $spending,
            'is_partial_year' => $this->is_partial_year,
            'data_status' => $this->is_partial_year ? 'partial_year' : 'published',
        ];
    }

    public function publicPayload(): array
    {
        $income = $this->number($this->income_budget);
        $incomeRealization = $this->number($this->income_realization);
        $spendingBudget = $this->number($this->spending_budget);
        $spendingRealization = $this->number($this->spending_realization);
        $receipt = $this->number($this->financing_receipt);
        $expenditure = $this->number($this->financing_expenditure);
        $financingNet = $receipt - $expenditure;
        $budgetBalance = $income - $spendingBudget;
        $realizationBalance = $incomeRealization - $spendingRealization;

        $programs = collect($this->programs ?? [])
            ->filter(fn ($item) => is_array($item) && trim((string) ($item['name'] ?? '')) !== '')
            ->map(function (array $item): array {
                $budget = $this->number($item['budget'] ?? 0);
                $realization = $this->number($item['realization'] ?? 0);

                return array_merge($item, [
                    'code' => (string) ($item['code'] ?? ''),
                    'category' => (string) ($item['category'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'budget' => $budget,
                    'realization' => $realization,
                    'percentage' => $this->percentage($realization, $budget),
                ]);
            })
            ->values()
            ->all();

        $revenue = collect($this->revenue ?? [])
            ->filter(fn ($item) => is_array($item) && trim((string) ($item['name'] ?? '')) !== '')
            ->map(function (array $item): array {
                $budget = $this->number($item['budget'] ?? 0);
                $realization = $this->number($item['realization'] ?? 0);

                return array_merge($item, [
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'short_name' => (string) ($item['short_name'] ?? $item['name'] ?? ''),
                    'budget' => $budget,
                    'realization' => $realization,
                    'variance' => $realization - $budget,
                    'percentage' => $this->percentage($realization, $budget),
                    'note' => $item['note'] ?? null,
                ]);
            })
            ->values()
            ->all();

        $spending = collect($this->spending ?? [])
            ->filter(fn ($item) => is_array($item) && trim((string) ($item['name'] ?? '')) !== '')
            ->map(function (array $item) use ($programs): array {
                $budget = $this->number($item['budget'] ?? 0);
                $realization = $this->number($item['realization'] ?? 0);
                $code = (string) ($item['code'] ?? '');

                return array_merge($item, [
                    'code' => $code,
                    'name' => (string) ($item['name'] ?? ''),
                    'short_name' => (string) ($item['short_name'] ?? $item['name'] ?? ''),
                    'description' => (string) ($item['description'] ?? ''),
                    'budget' => $budget,
                    'realization' => $realization,
                    'remaining' => $budget - $realization,
                    'percentage' => $this->percentage($realization, $budget),
                    'note' => $item['note'] ?? null,
                    'programs' => array_values(array_filter(
                        $programs,
                        fn (array $program): bool => ($program['category_code'] ?? '') === $code,
                    )),
                ]);
            })
            ->values()
            ->all();

        $topAllocation = collect($spending)->sortByDesc('budget')->first();
        $absorption = collect($spending)->filter(fn (array $item) => $item['budget'] > 0);
        $qualityNotes = preg_split('/\r\n|\r|\n/', (string) $this->data_quality_notes) ?: [];

        return [
            'summary' => [
                'year' => $this->year,
                'income' => $income,
                'income_realization' => $incomeRealization,
                'income_percentage' => $this->percentage($incomeRealization, $income),
                'spending' => $spendingBudget,
                'realization' => $spendingRealization,
                'percentage' => $this->percentage($spendingRealization, $spendingBudget),
                'remaining_budget' => $spendingBudget - $spendingRealization,
                'budget_balance' => $budgetBalance,
                'realization_balance' => $realizationBalance,
                'financing_net' => $financingNet,
                'estimated_silpa' => $realizationBalance + $financingNet,
                'is_partial_year' => $this->is_partial_year,
                'data_status' => $this->is_partial_year ? 'partial_year' : 'published',
            ],
            'revenue' => $revenue,
            'spending' => $spending,
            'programs' => $programs,
            'programs_available' => $programs !== [],
            'programs_note' => $programs === []
                ? ($this->programs_note ?: 'Rincian program/kegiatan belum tersedia untuk tahun ini.')
                : null,
            'quarters' => [],
            'quarters_available' => false,
            'quarters_note' => $this->quarters_note
                ?: 'Data APBDes dikelola dalam rekap tahunan; rincian triwulanan belum tersedia.',
            'financing' => [
                ['code' => '3.1', 'name' => 'Penerimaan Pembiayaan', 'type' => 'Penerimaan', 'amount' => $receipt],
                ['code' => '3.2', 'name' => 'Pengeluaran Pembiayaan', 'type' => 'Pengeluaran', 'amount' => $expenditure],
            ],
            'financing_summary' => [
                'receipt' => $receipt,
                'expenditure' => $expenditure,
                'net' => $financingNet,
                'percentage' => $this->percentage($expenditure, $receipt),
            ],
            'insights' => [
                'top_allocation' => $topAllocation,
                'best_absorption' => $absorption->sortByDesc('percentage')->first(),
                'lowest_absorption' => $absorption->sortBy('percentage')->first(),
            ],
            'data_quality' => array_values(array_filter(array_map('trim', $qualityNotes))),
            'problems' => $this->problems,
            'solutions' => $this->solutions,
            'description' => $this->description,
            'source_reference' => [
                'document' => $this->source_document,
                'pages' => $this->source_reference,
            ],
        ];
    }

    public static function defaultRevenue(): array
    {
        return [
            ['code' => '1.1', 'name' => 'Pendapatan Asli Desa', 'short_name' => 'PADes'],
            ['code' => '1.2.1', 'name' => 'Dana Desa', 'short_name' => 'Dana Desa'],
            ['code' => '1.2.2', 'name' => 'Alokasi Dana Desa', 'short_name' => 'ADD'],
            ['code' => '1.2.3', 'name' => 'Bagi Hasil Pajak dan Retribusi Daerah', 'short_name' => 'BHP'],
            ['code' => '1.2.4', 'name' => 'Bantuan Keuangan Provinsi', 'short_name' => 'BKK Provinsi'],
            ['code' => '1.2.5', 'name' => 'Bantuan Keuangan Kabupaten', 'short_name' => 'BKK Kabupaten'],
            ['code' => '1.3', 'name' => 'Pendapatan Lain-lain yang Sah', 'short_name' => 'Pendapatan Lain'],
        ];
    }

    public static function defaultSpending(): array
    {
        return [
            ['code' => '2.1', 'name' => 'Penyelenggaraan Pemerintahan Desa', 'short_name' => 'Pemerintahan', 'description' => 'Operasional pemerintahan, pelayanan administrasi, dan tata kelola desa.'],
            ['code' => '2.2', 'name' => 'Pelaksanaan Pembangunan Desa', 'short_name' => 'Pembangunan', 'description' => 'Infrastruktur, fasilitas umum, pendidikan, kesehatan, dan lingkungan.'],
            ['code' => '2.3', 'name' => 'Pembinaan Kemasyarakatan Desa', 'short_name' => 'Pembinaan', 'description' => 'Ketenteraman, kelembagaan masyarakat, kepemudaan, olahraga, seni, dan budaya.'],
            ['code' => '2.4', 'name' => 'Pemberdayaan Masyarakat Desa', 'short_name' => 'Pemberdayaan', 'description' => 'Peningkatan kapasitas, ekonomi masyarakat, pertanian, dan pelatihan.'],
            ['code' => '2.5', 'name' => 'Penanggulangan Bencana, Keadaan Darurat, dan Mendesak Desa', 'short_name' => 'Bencana/Darurat', 'description' => 'Penanggulangan bencana, keadaan darurat, dan kebutuhan mendesak desa.'],
        ];
    }

    private function number(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function percentage(float $value, float $basis): ?float
    {
        return $basis > 0 ? round($value / $basis * 100, 2) : null;
    }
}
