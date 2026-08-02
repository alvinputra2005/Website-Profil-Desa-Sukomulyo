<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Support\InventoryCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->query($request);
        $items = (clone $query)->latest()->paginate(25)->withQueryString();
        $summary = $this->summary(clone $query);
        $years = InventoryItem::distinct()->orderByDesc('acquisition_year')->pluck('acquisition_year');
        $mutationCount = InventoryMutation::query()
            ->when($request->filled('year'), fn ($q) => $q->whereYear('mutation_date', $request->integer('year')))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('item', fn ($i) => $i->where('category', $request->input('category'))))
            ->count();

        return view('admin.inventory.report', compact('items', 'summary', 'years', 'mutationCount'));
    }

    public function print(Request $request): View
    {
        $items = $this->query($request)->orderBy('category')->orderBy('acquisition_year')->orderBy('name')->get();
        $summary = $this->summary($this->query($request));
        return view('admin.inventory.print', compact('items', 'summary'));
    }

    public function csv(Request $request): StreamedResponse
    {
        $items = $this->query($request)->orderBy('category')->orderBy('name')->get();
        return Response::streamDownload(function () use ($items) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kategori', 'Nama Barang', 'Kode Barang', 'Nomor Register', 'Tahun', 'Asal-usul', 'Jumlah', 'Kondisi', 'Status', 'Nilai (Rp)', 'Keterangan']);
            foreach ($items as $item) {
                fputcsv($out, [InventoryCategory::label($item->category), $item->name, $item->item_code, $item->register_number, $item->acquisition_year, $item->origin, $item->quantity, $item->condition, $item->status, $item->value, $item->notes]);
            }
            fclose($out);
        }, 'laporan-inventaris-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function query(Request $request): Builder
    {
        return InventoryItem::query()
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('year'), fn ($q) => $q->where('acquisition_year', $request->integer('year')))
            ->when($request->filled('origin'), fn ($q) => $q->where('origin', $request->input('origin')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($search) use ($request) {
                $term = '%'.trim((string) $request->input('q')).'%';
                $search->where('name', 'like', $term)->orWhere('item_code', 'like', $term)->orWhere('register_number', 'like', $term);
            }));
    }

    private function summary(Builder $query): array
    {
        $items = $query->get(['category', 'origin', 'value', 'quantity', 'status']);
        return [
            'count' => $items->count(), 'quantity' => $items->sum('quantity'),
            'value' => $items->sum(fn ($item) => (float) $item->value),
            'active' => $items->where('status', 'Aktif')->count(),
            'by_category' => collect(InventoryCategory::all())->map(fn ($data, $slug) => [
                'label' => $data['label'], 'count' => $items->where('category', $slug)->count(),
                'value' => $items->where('category', $slug)->sum(fn ($item) => (float) $item->value),
            ])->values(),
            'by_origin' => collect(InventoryCategory::ORIGINS)->mapWithKeys(fn ($origin) => [$origin => $items->where('origin', $origin)->count()]),
        ];
    }
}
