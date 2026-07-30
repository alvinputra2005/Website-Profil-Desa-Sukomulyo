@extends('layouts.admin')
@section('title', 'Import Statistik')
@section('page-description', 'Validasi dan import dataset sensus JSON')
@section('content')
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
