@extends('layouts.admin')
@section('title', 'Preview Import Statistik')
@section('page-description', $import->original_filename)
@section('content')
@php
    $categoryMap = $categories->keyBy('slug');
    $canProcess = $validation['is_valid'] && $categories->isNotEmpty();
@endphp

<div class="row">
    <div class="col-sm-3">
        <div class="small-box bg-aqua">
            <div class="inner"><h3>{{ $validation['total_datasets'] }}</h3><p>Dataset</p></div>
            <div class="icon"><i class="fa fa-database"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-green">
            <div class="inner"><h3>{{ $validation['total_rows'] }}</h3><p>Baris Data</p></div>
            <div class="icon"><i class="fa fa-list"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-yellow">
            <div class="inner"><h3>{{ count($validation['warnings']) }}</h3><p>Peringatan</p></div>
            <div class="icon"><i class="fa fa-warning"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-red">
            <div class="inner"><h3>{{ count($validation['errors']) }}</h3><p>Kesalahan</p></div>
            <div class="icon"><i class="fa fa-times-circle"></i></div>
        </div>
    </div>
</div>

@if ($validation['errors'])
    <div class="callout callout-danger">
        <h4>File belum dapat diimpor</h4>
        <ul>
            @foreach ($validation['errors'] as $error)
                <li>
                    @if ($error['dataset_id'] ?? null)<strong>{{ $error['dataset_id'] }}:</strong> @endif
                    {{ $error['message'] }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if ($validation['warnings'])
    <div class="callout callout-warning">
        <h4>{{ count($validation['warnings']) }} peringatan ditemukan</h4>
        <ul>
            @foreach ($validation['warnings'] as $warning)
                <li>
                    @if ($warning['dataset_id'] ?? null)<strong>{{ $warning['dataset_id'] }}:</strong> @endif
                    {{ $warning['message'] }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if ($categories->isEmpty())
    <div class="callout callout-danger">
        <h4>Kategori statistik belum tersedia</h4>
        <p>Jalankan <code>php artisan db:seed --class=StatisticCategorySeeder</code> sebelum memproses import.</p>
    </div>
@endif

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-table"></i>
            Dataset {{ $validation['schema_name'] }} v{{ $validation['schema_version'] }}
        </h3>
        <div class="box-tools">
            <a href="{{ route('admin.statistics.import.create') }}" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <form method="post" action="{{ route('admin.statistics.import.process', $import->public_id) }}" data-confirm="Import dataset yang dipilih ke database?">
        @csrf
        <div class="box-body table-responsive no-padding">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px">Pilih</th>
                        <th>Dataset</th>
                        <th style="width: 90px">Periode</th>
                        <th style="min-width: 190px">Kategori</th>
                        <th style="min-width: 240px">Judul Pendek</th>
                        <th style="min-width: 120px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($validation['datasets'] as $index => $dataset)
                        @php
                            $suggestedCategory = $categoryMap->has($dataset['suggested_category'])
                                ? $dataset['suggested_category']
                                : 'lainnya';
                        @endphp
                        <tr class="{{ $dataset['requires_manual_review'] ? 'warning' : '' }}">
                            <td>
                                <input type="hidden" name="datasets[{{ $index }}][dataset_id]" value="{{ $dataset['dataset_id'] }}">
                                <input
                                    type="checkbox"
                                    name="datasets[{{ $index }}][selected]"
                                    value="1"
                                    aria-label="Pilih {{ $dataset['dataset_id'] }}"
                                    @checked(old("datasets.{$index}.selected", true))
                                >
                            </td>
                            <td>
                                <strong>{{ $dataset['title'] }}</strong><br>
                                <code>{{ $dataset['dataset_id'] }}</code>
                                <span class="text-muted">· {{ $dataset['row_count'] }} baris</span>
                                @if ($dataset['warnings'])
                                    <ul class="text-warning" style="margin-top: 8px; padding-left: 18px">
                                        @foreach ($dataset['warnings'] as $warning)
                                            <li>{{ $warning['message'] }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>{{ $dataset['period'] }}</td>
                            <td>
                                <select name="datasets[{{ $index }}][category_slug]" class="form-control input-sm" required>
                                    @foreach ($categories as $category)
                                        <option
                                            value="{{ $category->slug }}"
                                            @selected(old("datasets.{$index}.category_slug", $suggestedCategory) === $category->slug)
                                        >
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="datasets[{{ $index }}][short_title]"
                                    class="form-control input-sm"
                                    maxlength="255"
                                    value="{{ old("datasets.{$index}.short_title", $dataset['short_title']) }}"
                                >
                            </td>
                            <td>
                                @if ($dataset['requires_manual_review'])
                                    <input type="hidden" name="datasets[{{ $index }}][status]" value="draft">
                                    <span class="label label-warning">Perlu ditinjau</span>
                                @else
                                    <select name="datasets[{{ $index }}][status]" class="form-control input-sm" required>
                                        <option value="draft" @selected(old("datasets.{$index}.status", 'draft') === 'draft')>Draf</option>
                                        <option value="published" @selected(old("datasets.{$index}.status") === 'published')>Publik</option>
                                    </select>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Tidak ada dataset yang dapat ditampilkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <span class="text-muted">
                Dataset dengan peringatan selalu disimpan sebagai <strong>needs_review</strong>.
            </span>
            <button class="btn btn-social btn-success pull-right" @disabled(! $canProcess)>
                <i class="fa fa-database"></i> Import Dataset Terpilih
            </button>
        </div>
    </form>
</div>
@endsection
