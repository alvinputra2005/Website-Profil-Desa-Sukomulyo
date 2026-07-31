@extends('layouts.admin')
@section('title', 'Import Statistik')
@section('page-description', 'Import dataset statistik dari Excel atau JSON')
@section('content')
<div class="box box-success">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-excel-o"></i> Import Excel Menjadi Dataset</h3></div>
    <form method="post" action="{{ route('admin.statistics.import.excel') }}" enctype="multipart/form-data">
        @csrf
        <div class="box-body">
            <div class="callout callout-info"><p>Baris pertama Excel akan menjadi nama kolom. Setiap baris berikutnya otomatis menjadi baris dataset, dan tipe kolom angka akan dideteksi otomatis.</p></div>
            <div class="row">
                <div class="form-group col-md-4 @error('category_id') has-error @enderror"><label for="excel-category">Kategori</label><select id="excel-category" name="category_id" class="form-control select2" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', request('category')) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-5 @error('title') has-error @enderror"><label for="excel-title">Judul Dataset</label><input id="excel-title" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">@error('title')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-3"><label for="excel-short-title">Nama pada Dropdown</label><input id="excel-short-title" name="short_title" class="form-control" value="{{ old('short_title') }}" maxlength="255"></div>
            </div>
            <div class="row">
                <div class="form-group col-md-2 @error('period') has-error @enderror"><label for="excel-period">Periode</label><input id="excel-period" name="period" class="form-control" value="{{ old('period', now()->year) }}" required maxlength="50">@error('period')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-2"><label for="excel-unit">Satuan</label><input id="excel-unit" name="unit" class="form-control" value="{{ old('unit', 'data') }}" required maxlength="50"></div>
                <div class="form-group col-md-3"><label for="excel-status">Status</label><select id="excel-status" name="status" class="form-control"><option value="draft" @selected(old('status', 'draft') === 'draft')>Draf</option><option value="needs_review" @selected(old('status') === 'needs_review')>Perlu Ditinjau</option><option value="published" @selected(old('status') === 'published')>Terbit</option></select></div>
                <div class="form-group col-md-5"><label for="excel-source">Sumber Data</label><input id="excel-source" name="source" class="form-control" value="{{ old('source') }}" maxlength="255"></div>
            </div>
            <div class="form-group @error('file') has-error @enderror"><label for="excel-file">File Excel</label><input id="excel-file" type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required><p class="help-block">Format XLSX, XLS, atau CSV; maksimal 20 MB.</p>@error('file')<span class="help-block">{{ $message }}</span>@enderror</div>
        </div>
        <div class="box-footer"><a href="{{ route('admin.statistics.categories.create-category') }}" class="btn btn-default"><i class="fa fa-plus"></i> Buat Kategori Baru</a><button class="btn btn-success pull-right"><i class="fa fa-upload"></i> Import dan Buat Dataset</button></div>
    </form>
</div>
<div class="row">
    <div class="col-md-7">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-upload"></i> Unggah Dataset Normalisasi</h3>
            </div>
            <form method="post" action="{{ route('admin.statistics.import.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="box-body">
                    <div class="callout callout-info">
                        <h4>Alur import aman</h4>
                        <p>File akan divalidasi dan ditampilkan sebagai preview terlebih dahulu. Data belum masuk ke database sebelum Anda mengonfirmasi halaman berikutnya.</p>
                    </div>
                    <div class="form-group @error('statistics_file') has-error @enderror">
                        <label for="statistics_file">File JSON Statistik</label>
                        <input
                            id="statistics_file"
                            type="file"
                            name="statistics_file"
                            class="form-control"
                            accept=".json,application/json"
                            required
                        >
                        <p class="help-block">Schema yang didukung: normalized_census_statistics versi 2.0. Ukuran maksimal 20 MB.</p>
                        @error('statistics_file')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="box-footer">
                    <button class="btn btn-social btn-info pull-right">
                        <i class="fa fa-check-circle"></i> Validasi File
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> Import Terakhir</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Dataset</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentImports as $recentImport)
                            @php
                                $statusClass = match ($recentImport->status) {
                                    'completed' => 'label-success',
                                    'validated' => 'label-info',
                                    'invalid', 'failed' => 'label-danger',
                                    default => 'label-warning',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.statistics.import.preview', $recentImport->public_id) }}">
                                        {{ \Illuminate\Support\Str::limit($recentImport->original_filename, 30) }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $recentImport->created_at?->format('d-m-Y H:i') }}</small>
                                </td>
                                <td>{{ $recentImport->total_datasets }}</td>
                                <td><span class="label {{ $statusClass }}">{{ $recentImport->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada riwayat import.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
