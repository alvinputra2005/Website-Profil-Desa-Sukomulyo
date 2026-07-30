@extends('layouts.admin')

@section('title', 'Statistik '.$category->name)
@section('page-description', 'Lihat dan kelola setiap jenis data statistik '.$category->name)

@php
    $statusLabels = ['draft' => 'Draf', 'published' => 'Terbit', 'needs_review' => 'Perlu Ditinjau', 'archived' => 'Arsip'];
    $statusClasses = ['draft' => 'label-default', 'published' => 'label-success', 'needs_review' => 'label-warning', 'archived' => 'label-danger'];
    $formatValue = static function (mixed $value, string $type = 'string'): string {
        if ($value === null || $value === '') return '—';
        return match ($type) {
            'integer' => number_format((float) $value, 0, ',', '.'),
            'percentage' => number_format((float) $value, 2, ',', '.').'%',
            default => (string) $value,
        };
    };
@endphp

@section('content')
<div class="statistics-filter-box box box-default">
    <div class="box-body">
        <form method="get" action="{{ route('admin.statistics.categories.show', $category->slug) }}" class="statistics-category-filters">
            <div class="form-group">
                <label for="data">Pilih Data Statistik</label>
                <select id="data" name="data" class="form-control select2" onchange="this.form.submit()">
                    @forelse ($types as $type)
                        <option value="{{ $type['key'] }}" @selected($selectedType === $type['key'])>{{ $type['label'] }}</option>
                    @empty
                        <option value="">Belum ada data</option>
                    @endforelse
                </select>
            </div>
            <div class="form-group">
                <label for="period">Tahun/Periode</label>
                <select id="period" name="period" class="form-control" onchange="this.form.submit()">
                    @forelse ($periods as $period)
                        <option value="{{ $period }}" @selected($selectedPeriod === (string) $period)>{{ $period }}</option>
                    @empty
                        <option value="">—</option>
                    @endforelse
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" onchange="this.form.submit()">
                    <option value="all" @selected($status === 'all')>Semua Status</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="statistics-category-filter-action">
                <a href="{{ route('admin.statistics.import.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Import Data</a>
            </div>
        </form>
    </div>
</div>

<div class="statistics-category-summary">
    @foreach ([
        ['Total Data Kategori', $summary['datasets'], 'Jenis data tersedia', 'fa-file-text', 'statistics-summary-blue'],
        ['Total Baris Data', $summary['rows'], 'Total baris seluruh data', 'fa-database', 'statistics-summary-green'],
        ['Periode Tersedia', $summary['periods'], 'Periode data tersedia', 'fa-calendar', 'statistics-summary-yellow'],
        ['Data Terbit', $summary['published'], 'Data sudah dipublikasikan', 'fa-check-circle', 'statistics-summary-purple'],
    ] as [$label, $value, $hint, $icon, $class])
        <div class="statistics-summary-card {{ $class }}">
            <span class="statistics-summary-icon"><i class="fa {{ $icon }}"></i></span>
            <div><span>{{ $label }}</span><strong>{{ number_format($value, 0, ',', '.') }}</strong><small>{{ $hint }}</small></div>
        </div>
    @endforeach
</div>

@if ($dataset)
    <div class="box box-success statistics-dataset-card">
        <div class="box-header with-border statistics-dataset-heading">
            <div>
                <h3 class="box-title">
                    {{ $dataset->short_title ?: $dataset->title }}
                    <span class="label {{ $statusClasses[$dataset->status] ?? 'label-default' }}">{{ $statusLabels[$dataset->status] ?? ucfirst($dataset->status) }}</span>
                </h3>
                <dl class="statistics-dataset-meta">
                    <div><dt>Kategori</dt><dd>{{ $category->name }}</dd></div>
                    <div><dt>Periode</dt><dd>{{ $dataset->period ?: $dataset->year }}</dd></div>
                    <div><dt>Sumber</dt><dd>{{ data_get($dataset->source_metadata_json, 'source_note', $dataset->source ?: '—') }}</dd></div>
                </dl>
            </div>
            <div class="statistics-dataset-actions">
                @if ($dataset->status === 'published')
                    <a href="{{ route('data-statistik.imported.show', ['category' => $category->slug, 'dataset' => $dataset->slug]) }}" target="_blank" rel="noopener" class="btn btn-default"><i class="fa fa-eye"></i> Lihat Publik</a>
                @endif
                <a href="{{ route('admin.statistics.categories.edit', ['category' => $category->slug, 'dataset' => $dataset->slug]) }}" class="btn btn-warning"><i class="fa fa-pencil"></i> Edit Data</a>
            </div>
        </div>
        <div class="box-body">
            @if ($dataset->description)<p class="statistics-dataset-description">{{ $dataset->description }}</p>@endif
            <div class="table-responsive statistics-dataset-table-wrap">
                <table class="table table-bordered table-striped statistics-dataset-table">
                    <thead><tr><th class="statistics-row-number">No</th>
                        @foreach ($dataset->columns_json ?? [] as $column)
                            <th>{{ $column['label'] ?? $column['key'] }} @if (($column['type'] ?? null) === 'percentage')<small>(%)</small>@endif</th>
                        @endforeach
                        <th class="statistics-row-action">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($dataset->rows as $row)
                            <tr><td class="text-center">{{ $loop->iteration }}</td>
                                @foreach ($dataset->columns_json ?? [] as $column)
                                    @php($key = $column['key'])
                                    @php($value = match ($key) { 'area_code' => $row->area_code, 'area_name' => $row->area_name, default => data_get($row->values_json, $key) })
                                    <td class="{{ in_array($column['type'] ?? '', ['integer', 'percentage'], true) ? 'text-right' : '' }}">{{ $formatValue($value, $column['type'] ?? 'string') }}</td>
                                @endforeach
                                <td class="text-center"><a href="{{ route('admin.statistics.categories.edit', ['category' => $category->slug, 'dataset' => $dataset->slug]).'#row-'.$row->id }}" class="btn btn-warning btn-xs" title="Edit baris {{ $loop->iteration }}"><i class="fa fa-pencil"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($dataset->columns_json ?? []) + 2 }}" class="empty-state">Dataset ini belum memiliki baris data.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($dataset->totals_json)
                        <tfoot><tr>
                            <th colspan="{{ collect($dataset->columns_json ?? [])->whereIn('key', ['area_code', 'area_name'])->count() + 1 }}">JUMLAH TOTAL</th>
                            @foreach (collect($dataset->columns_json ?? [])->reject(fn ($column) => in_array($column['key'] ?? '', ['area_code', 'area_name'], true)) as $column)
                                <th class="text-right">{{ $formatValue(data_get($dataset->totals_json, $column['key']), $column['type'] ?? 'string') }}</th>
                            @endforeach
                            <th></th>
                        </tr></tfoot>
                    @endif
                </table>
            </div>
            <p class="statistics-table-count">Menampilkan {{ $dataset->rows->count() }} baris data.</p>
        </div>
    </div>
@else
    <div class="box box-warning"><div class="box-body empty-state statistics-empty-state">
        <i class="fa fa-filter"></i><h4>Data tidak ditemukan</h4>
        <p>@if ($datasets->isEmpty()) Belum ada dataset pada kategori {{ $category->name }}. Import data statistik untuk mulai mengelolanya. @else Tidak ada dataset yang cocok dengan kombinasi jenis data, periode, dan status tersebut. @endif</p>
    </div></div>
@endif
@endsection
