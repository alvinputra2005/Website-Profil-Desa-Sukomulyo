<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveInventoryMutationRequest;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Services\ActivityLogger;
use App\Support\InventoryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryMutationController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function create(string $category, InventoryItem $item): View
    {
        $this->ensure($category, $item);
        return view('admin.inventory.mutation-form', ['category' => $category, 'categoryData' => InventoryCategory::get($category), 'item' => $item, 'mutation' => new InventoryMutation]);
    }

    public function store(SaveInventoryMutationRequest $request, string $category, InventoryItem $item): RedirectResponse
    {
        $this->ensure($category, $item);
        DB::transaction(function () use ($request, $item) {
            $mutation = $item->mutations()->create($request->validated() + ['created_by' => $request->user()->id]);
            $this->syncItem($item, $mutation);
            $this->logger->log('tambah', 'mutasi inventaris', $mutation, null, $mutation->toArray());
        });
        return redirect()->route('admin.inventory.show', [$category, $item])->with('success', 'Mutasi inventaris berhasil dicatat.');
    }

    public function edit(string $category, InventoryItem $item, InventoryMutation $mutation): View
    {
        $this->ensure($category, $item, $mutation);
        return view('admin.inventory.mutation-form', ['category' => $category, 'categoryData' => InventoryCategory::get($category), 'item' => $item, 'mutation' => $mutation]);
    }

    public function update(SaveInventoryMutationRequest $request, string $category, InventoryItem $item, InventoryMutation $mutation): RedirectResponse
    {
        $this->ensure($category, $item, $mutation);
        DB::transaction(function () use ($request, $item, $mutation) {
            $old = $mutation->toArray();
            $mutation->update($request->validated());
            $this->syncFromLatest($item);
            $this->logger->log('ubah', 'mutasi inventaris', $mutation, $old, $mutation->fresh()->toArray());
        });
        return redirect()->route('admin.inventory.show', [$category, $item])->with('success', 'Mutasi inventaris berhasil diperbarui.');
    }

    public function destroy(string $category, InventoryItem $item, InventoryMutation $mutation): RedirectResponse
    {
        $this->ensure($category, $item, $mutation);
        DB::transaction(function () use ($item, $mutation) {
            $old = $mutation->toArray();
            $this->logger->log('hapus', 'mutasi inventaris', $mutation, $old);
            $mutation->delete();
            $this->syncFromLatest($item);
        });
        return redirect()->route('admin.inventory.show', [$category, $item])->with('success', 'Catatan mutasi berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, string $category, InventoryItem $item): RedirectResponse
    {
        $this->ensure($category, $item);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('inventory_mutations', 'id')->where('inventory_item_id', $item->id)],
        ]);

        $mutations = InventoryMutation::where('inventory_item_id', $item->id)->whereKey($data['ids'])->get();

        DB::transaction(function () use ($item, $mutations): void {
            $mutations->each(function (InventoryMutation $mutation): void {
                $old = $mutation->toArray();
                $this->logger->log('hapus', 'mutasi inventaris', $mutation, $old);
                $mutation->delete();
            });
            $this->syncFromLatest($item);
        });

        return redirect()->route('admin.inventory.show', [$category, $item])->with('success', $mutations->count().' catatan mutasi berhasil dihapus.');
    }

    private function syncFromLatest(InventoryItem $item): void
    {
        $latest = $item->mutations()->orderByDesc('mutation_date')->orderByDesc('id')->first();
        if ($latest) $this->syncItem($item, $latest);
        else $item->update(['status' => 'Aktif']);
    }

    private function syncItem(InventoryItem $item, InventoryMutation $mutation): void
    {
        $terminal = in_array($mutation->mutation_type, ['Masih Baik Disumbangkan', 'Barang Rusak Disumbangkan', 'Masih Baik Dijual', 'Barang Rusak Dijual', 'Hibah', 'Musnah', 'Hilang'], true) || $mutation->asset_status === 'Hapus';
        $condition = match ($mutation->asset_status) { 'Rusak' => 'Rusak Berat', 'Baik', 'Diperbaiki' => 'Baik', default => $item->condition };
        $item->update(['status' => $terminal ? 'Dimutasi' : 'Aktif', 'condition' => $condition, 'updated_by' => auth()->id()]);
    }

    private function ensure(string $category, InventoryItem $item, ?InventoryMutation $mutation = null): void
    {
        if (! InventoryCategory::get($category) || $item->category !== $category || ($mutation && $mutation->inventory_item_id !== $item->id)) abort(404);
    }
}
