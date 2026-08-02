<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveInventoryItemRequest;
use App\Models\InventoryItem;
use App\Services\ActivityLogger;
use App\Support\InventoryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(Request $request, string $category): View
    {
        $categoryData = $this->category($category);
        $items = InventoryItem::query()->where('category', $category)
            ->with('updatedBy')->withCount('mutations')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($query) use ($request) {
                $term = '%'.trim((string) $request->input('q')).'%';
                $query->where('name', 'like', $term)->orWhere('item_code', 'like', $term)->orWhere('register_number', 'like', $term);
            }))
            ->when($request->filled('year'), fn ($q) => $q->where('acquisition_year', $request->integer('year')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()->paginate(20)->withQueryString();

        $years = InventoryItem::where('category', $category)->distinct()->orderByDesc('acquisition_year')->pluck('acquisition_year');
        return view('admin.inventory.index', compact('category', 'categoryData', 'items', 'years'));
    }

    public function create(string $category): View
    {
        return view('admin.inventory.form', ['category' => $category, 'categoryData' => $this->category($category), 'item' => new InventoryItem]);
    }

    public function store(SaveInventoryItemRequest $request, string $category): RedirectResponse
    {
        $data = $this->payload($request, $category);
        $item = InventoryItem::create($data + ['category' => $category, 'status' => 'Aktif', 'updated_by' => $request->user()->id]);
        $this->logger->log('tambah', 'inventaris', $item, null, $item->toArray());
        return redirect()->route('admin.inventory.index', $category)->with('success', 'Data inventaris berhasil ditambahkan.');
    }

    public function show(string $category, InventoryItem $item): View
    {
        $this->ensureCategory($category, $item);
        $item->load(['mutations.createdBy', 'updatedBy']);
        return view('admin.inventory.show', ['category' => $category, 'categoryData' => $this->category($category), 'item' => $item]);
    }

    public function edit(string $category, InventoryItem $item): View
    {
        $this->ensureCategory($category, $item);
        return view('admin.inventory.form', ['category' => $category, 'categoryData' => $this->category($category), 'item' => $item]);
    }

    public function update(SaveInventoryItemRequest $request, string $category, InventoryItem $item): RedirectResponse
    {
        $this->ensureCategory($category, $item);
        $old = $item->toArray();
        $item->update($this->payload($request, $category) + ['updated_by' => $request->user()->id]);
        $this->logger->log('ubah', 'inventaris', $item, $old, $item->fresh()->toArray());
        return redirect()->route('admin.inventory.index', $category)->with('success', 'Data inventaris berhasil diperbarui.');
    }

    public function destroy(string $category, InventoryItem $item): RedirectResponse
    {
        $this->ensureCategory($category, $item);
        $old = $item->toArray();
        $this->logger->log('hapus', 'inventaris', $item, $old);
        $item->delete();
        return redirect()->route('admin.inventory.index', $category)->with('success', 'Data inventaris dan riwayat mutasinya berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, string $category): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('inventory_items', 'id')],
        ]);

        $items = InventoryItem::where('category', $category)->whereKey($data['ids'])->get();

        DB::transaction(function () use ($items, $category): void {
            $items->each(function (InventoryItem $item) use ($category): void {
                $this->ensureCategory($category, $item);
                $old = $item->toArray();
                $this->logger->log('hapus', 'inventaris', $item, $old);
                $item->delete();
            });
        });

        return redirect()->route('admin.inventory.index', $category)->with('success', $items->count().' data inventaris berhasil dihapus.');
    }

    private function payload(SaveInventoryItemRequest $request, string $category): array
    {
        $data = $request->safe()->only(['name', 'item_code', 'register_number', 'acquisition_year', 'origin', 'value', 'quantity', 'condition', 'notes']);
        $allowed = array_keys(InventoryCategory::get($category)['fields']);
        $data['details'] = collect($request->validated('details', []))->only($allowed)->filter(fn ($v) => $v !== null && $v !== '')->all();
        return $data;
    }

    private function category(string $category): array { return InventoryCategory::get($category) ?? abort(404); }
    private function ensureCategory(string $category, InventoryItem $item): void { if ($item->category !== $category) abort(404); }
}
