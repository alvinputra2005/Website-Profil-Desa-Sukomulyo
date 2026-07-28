<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SavePopulationYearlySnapshotRequest;
use App\Models\PopulationYearlySnapshot;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PopulationYearlySnapshotController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(): View
    {
        $this->authorize('viewAny', PopulationYearlySnapshot::class);

        return view('admin.population.yearly-snapshots.index', [
            'snapshots' => PopulationYearlySnapshot::query()
                ->orderByDesc('year')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PopulationYearlySnapshot::class);

        return view('admin.population.yearly-snapshots.form', [
            'snapshot' => new PopulationYearlySnapshot([
                'year' => config('village.population_year'),
                'reference_date' => now(),
            ]),
        ]);
    }

    public function store(SavePopulationYearlySnapshotRequest $request): RedirectResponse
    {
        $this->authorize('create', PopulationYearlySnapshot::class);
        $snapshot = PopulationYearlySnapshot::create($this->snapshotData($request->validated()));
        $this->logger->log('created', 'statistik_tahunan', $snapshot, null, $snapshot->toArray());

        return redirect()
            ->route('admin.population.yearly-snapshots.edit', $snapshot)
            ->with('success', 'Snapshot statistik tahunan berhasil ditambahkan.');
    }

    public function edit(PopulationYearlySnapshot $yearlySnapshot): View
    {
        $this->authorize('update', $yearlySnapshot);

        return view('admin.population.yearly-snapshots.form', [
            'snapshot' => $yearlySnapshot,
        ]);
    }

    public function update(
        SavePopulationYearlySnapshotRequest $request,
        PopulationYearlySnapshot $yearlySnapshot,
    ): RedirectResponse {
        $this->authorize('update', $yearlySnapshot);
        $old = $yearlySnapshot->toArray();
        $yearlySnapshot->update($this->snapshotData($request->validated()));
        $this->logger->log('updated', 'statistik_tahunan', $yearlySnapshot, $old, $yearlySnapshot->fresh()->toArray());

        return back()->with('success', 'Snapshot statistik tahunan berhasil diperbarui.');
    }

    public function destroy(PopulationYearlySnapshot $yearlySnapshot): RedirectResponse
    {
        $this->authorize('delete', $yearlySnapshot);
        $this->logger->log('deleted', 'statistik_tahunan', $yearlySnapshot, $yearlySnapshot->toArray());
        $yearlySnapshot->delete();

        return redirect()
            ->route('admin.population.yearly-snapshots.index')
            ->with('success', 'Snapshot statistik tahunan berhasil dihapus.');
    }

    public function togglePublication(PopulationYearlySnapshot $yearlySnapshot): RedirectResponse
    {
        $this->authorize('update', $yearlySnapshot);
        $old = $yearlySnapshot->toArray();
        $yearlySnapshot->update(['is_published' => ! $yearlySnapshot->is_published]);
        $this->logger->log(
            $yearlySnapshot->is_published ? 'published' : 'unpublished',
            'statistik_tahunan',
            $yearlySnapshot,
            $old,
            $yearlySnapshot->fresh()->toArray(),
        );

        return back()->with(
            'success',
            $yearlySnapshot->is_published
                ? 'Snapshot sekarang ditampilkan pada halaman publik.'
                : 'Snapshot disembunyikan dari halaman publik.',
        );
    }

    private function snapshotData(array $data): array
    {
        return collect($data)
            ->only(['year', 'male_count', 'female_count', 'reference_date', 'source', 'notes'])
            ->merge(['is_published' => (bool) ($data['is_published'] ?? false)])
            ->all();
    }
}
