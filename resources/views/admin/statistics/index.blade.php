@extends('layouts.admin')

@section('title', 'Ringkasan Statistik')
@section('page-description', 'Ringkasan statistik warga terkini dan data sensus/periodik')

@section('content')
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-folder-open"></i> Kategori Dataset</h3>
        <div class="box-tools"><a href="{{ route('admin.statistics.categories.create-category') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Kategori</a> <a href="{{ route('admin.statistics.import.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> Import Dataset</a></div>
    </div>
    <div class="box-body">
        @php($allCategories = \App\Models\StatisticCategory::query()->where('is_active', true)->withCount('datasets')->orderBy('display_order')->orderBy('name')->get())
        @if ($allCategories->isEmpty())
            <p class="text-muted">Belum ada kategori dataset. Buat kategori pertama untuk mulai menambahkan data.</p>
        @else
            <div class="list-group">
                @foreach ($allCategories as $category)
                    <a class="list-group-item" href="{{ route('admin.statistics.categories.show', $category->slug) }}"><i class="fa {{ $category->icon ?: 'fa-table' }} fa-fw"></i> {{ $category->name }} <span class="badge">{{ $category->datasets_count }} dataset</span></a>
                @endforeach
            </div>
        @endif
    </div>
</div>
<div class="statistics-category-summary">
    @foreach ([
        ['Indikator Warga', $populationIndicatorCount, 'Indikator dengan data tersedia', 'fa-users', 'statistics-summary-blue'],
        ['Kategori Sensus', $categoryCount, 'Kategori dengan baris data', 'fa-folder-open', 'statistics-summary-green'],
        ['Dataset Sensus', $datasetCount, 'Dataset yang memiliki baris', 'fa-database', 'statistics-summary-yellow'],
        ['Data Terbit', $publishedCount, 'Dataset terbit dengan baris data', 'fa-check-circle', 'statistics-summary-purple'],
    ] as [$label, $value, $hint, $icon, $class])
        <div class="statistics-summary-card {{ $class }}">
            <span class="statistics-summary-icon"><i class="fa {{ $icon }}"></i></span>
            <div><span>{{ $label }}</span><strong>{{ number_format($value, 0, ',', '.') }}</strong><small>{{ $hint }}</small></div>
        </div>
    @endforeach
</div>

@if ($populationIndicatorCount > 0)
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Statistik Warga Terkini</h3></div>
        <div class="box-body">
            <p>Indikator hanya ditampilkan jika sumber datanya tersedia dan berisi data valid.</p>
            <a class="btn btn-primary" href="{{ route('admin.statistics.population.show') }}">Lihat Penduduk Terkini</a>
        </div>
    </div>
@endif

<div class="box box-success">
    <div class="box-header with-border"><h3 class="box-title">Statistik Sensus/Periodik</h3></div>
    <div class="box-body">
        <p>Pilih kategori statistik dari sidebar untuk memfilter jenis data, periode, dan status.</p>
        @if ($categoryCount === 0)
            <div class="empty-state">Belum ada kategori sensus yang memiliki baris data.</div>
        @endif
    </div>
</div>
@endsection
