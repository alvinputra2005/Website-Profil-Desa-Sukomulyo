<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveApbdesRequest;
use App\Models\Apbdes;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApbdesController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(): View
    {
        $this->authorize('viewAny', Apbdes::class);

        return view('admin.apbdes.index', [
            'budgets' => Apbdes::query()
                ->with('updatedBy')
                ->orderByDesc('year')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Apbdes::class);
        $year = max(now()->year, ((int) Apbdes::query()->max('year')) + 1);

        return $this->form(new Apbdes([
            'year' => $year,
            'title' => 'APBDes Desa Sukomulyo Tahun '.$year,
            'status' => 'draft',
            'is_partial_year' => true,
            'income_budget' => 0,
            'income_realization' => 0,
            'spending_budget' => 0,
            'spending_realization' => 0,
            'financing_receipt' => 0,
            'financing_expenditure' => 0,
            'revenue' => Apbdes::defaultRevenue(),
            'spending' => Apbdes::defaultSpending(),
            'programs' => [],
            'source_document' => 'LPPD Desa Sukomulyo',
        ]));
    }

    public function store(SaveApbdesRequest $request): RedirectResponse
    {
        $this->authorize('create', Apbdes::class);

        $budget = DB::transaction(function () use ($request): Apbdes {
            $budget = Apbdes::create($this->budgetData($request->validated()));
            $this->logger->log('created', 'apbdes', $budget, null, $budget->toArray());

            return $budget;
        });

        return redirect()
            ->route('admin.apbdes.edit', $budget)
            ->with('success', 'Data APBDes tahun '.$budget->year.' berhasil ditambahkan.');
    }

    public function edit(Apbdes $apbdes): View
    {
        $this->authorize('update', $apbdes);

        return $this->form($apbdes);
    }

    public function update(SaveApbdesRequest $request, Apbdes $apbdes): RedirectResponse
    {
        $this->authorize('update', $apbdes);

        DB::transaction(function () use ($request, $apbdes): void {
            $old = $apbdes->toArray();
            $apbdes->update($this->budgetData($request->validated(), $apbdes));
            $this->logger->log('updated', 'apbdes', $apbdes, $old, $apbdes->fresh()->toArray());
        });

        return back()->with('success', 'Data APBDes tahun '.$apbdes->year.' berhasil diperbarui.');
    }

    public function destroy(Apbdes $apbdes): RedirectResponse
    {
        $this->authorize('delete', $apbdes);
        $old = $apbdes->toArray();
        $year = $apbdes->year;

        DB::transaction(function () use ($apbdes, $old): void {
            $this->logger->log('deleted', 'apbdes', $apbdes, $old);
            $apbdes->delete();
        });

        return redirect()
            ->route('admin.apbdes.index')
            ->with('success', 'Data APBDes tahun '.$year.' berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Apbdes::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('apbdes', 'id')],
        ]);

        $budgets = Apbdes::whereKey($data['ids'])->get();

        DB::transaction(function () use ($budgets): void {
            $budgets->each(function (Apbdes $apbdes): void {
                $this->authorize('delete', $apbdes);
                $old = $apbdes->toArray();
                $this->logger->log('deleted', 'apbdes', $apbdes, $old);
                $apbdes->delete();
            });
        });

        return redirect()
            ->route('admin.apbdes.index')
            ->with('success', $budgets->count().' data APBDes berhasil dihapus.');
    }

    public function togglePublication(Apbdes $apbdes): RedirectResponse
    {
        $this->authorize('update', $apbdes);
        $old = $apbdes->toArray();
        $publish = $apbdes->status !== 'published';

        $apbdes->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? now() : null,
            'updated_by' => auth()->id(),
        ]);

        $this->logger->log(
            $publish ? 'published' : 'unpublished',
            'apbdes',
            $apbdes,
            $old,
            $apbdes->fresh()->toArray(),
        );

        return back()->with(
            'success',
            $publish
                ? 'APBDes tahun '.$apbdes->year.' sekarang tampil di halaman publik.'
                : 'APBDes tahun '.$apbdes->year.' disembunyikan dari halaman publik.',
        );
    }

    private function form(Apbdes $apbdes): View
    {
        return view('admin.apbdes.form', [
            'apbdes' => $apbdes,
            'revenueRows' => old('revenue', $apbdes->revenue ?: Apbdes::defaultRevenue()),
            'spendingRows' => old('spending', $apbdes->spending ?: Apbdes::defaultSpending()),
            'programRows' => old('programs', $apbdes->programs ?: []),
            'spendingCategories' => collect(old('spending', $apbdes->spending ?: Apbdes::defaultSpending()))
                ->mapWithKeys(fn (array $item) => [
                    (string) ($item['code'] ?? '') => (string) ($item['short_name'] ?? $item['name'] ?? ''),
                ])
                ->filter()
                ->all(),
        ]);
    }

    private function budgetData(array $data, ?Apbdes $current = null): array
    {
        $status = $data['status'];

        return collect($data)
            ->only([
                'year',
                'title',
                'description',
                'status',
                'income_budget',
                'income_realization',
                'spending_budget',
                'spending_realization',
                'financing_receipt',
                'financing_expenditure',
                'problems',
                'solutions',
                'programs_note',
                'quarters_note',
                'data_quality_notes',
                'source_document',
                'source_reference',
            ])
            ->merge([
                'is_partial_year' => (bool) ($data['is_partial_year'] ?? false),
                'revenue' => $this->normalizeRows($data['revenue'] ?? [], [
                    'code', 'name', 'short_name', 'budget', 'realization', 'note',
                ]),
                'spending' => $this->normalizeRows($data['spending'] ?? [], [
                    'code', 'name', 'short_name', 'description', 'budget', 'realization', 'note',
                ]),
                'programs' => $this->normalizeRows($data['programs'] ?? [], [
                    'code', 'category_code', 'category', 'name', 'description', 'budget', 'realization',
                ], true),
                'published_at' => $status === 'published'
                    ? ($current?->published_at ?? now())
                    : null,
                'updated_by' => auth()->id(),
            ])
            ->all();
    }

    private function normalizeRows(array $rows, array $keys, bool $skipEmptyNames = false): array
    {
        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->when(
                $skipEmptyNames,
                fn ($items) => $items->filter(fn (array $row) => trim((string) ($row['name'] ?? '')) !== ''),
            )
            ->map(function (array $row) use ($keys): array {
                $normalized = collect($row)->only($keys)->all();

                foreach (['budget', 'realization'] as $numeric) {
                    if (array_key_exists($numeric, $normalized)) {
                        $normalized[$numeric] = (float) ($normalized[$numeric] ?: 0);
                    }
                }

                return $normalized;
            })
            ->values()
            ->all();
    }
}
