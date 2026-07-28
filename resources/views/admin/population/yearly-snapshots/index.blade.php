@extends('layouts.admin')
@section('title','Statistik Tahunan')
@section('page-description','Snapshot tahunan untuk grafik pertumbuhan penduduk publik')
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-line-chart"></i> Data Penduduk Tahunan</h3>
        <div class="box-tools">
            <a href="{{ route('admin.population.yearly-snapshots.create') }}" class="btn btn-social btn-info btn-sm">
                <i class="fa fa-plus"></i> Tambah Snapshot
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class="callout callout-info">
            <h4>Data yang dapat dipertanggungjawabkan</h4>
            <p>Hanya snapshot berstatus publik yang tampil pada grafik warga. Isikan tanggal referensi dan sumber setiap tahun agar angka mudah diverifikasi.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover population-table">
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Laki-laki</th>
                        <th>Perempuan</th>
                        <th>Total</th>
                        <th>Tanggal Referensi</th>
                        <th>Sumber</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($snapshots as $snapshot)
                        <tr>
                            <td><strong>{{ $snapshot->year }}</strong></td>
                            <td>{{ number_format($snapshot->male_count, 0, ',', '.') }}</td>
                            <td>{{ number_format($snapshot->female_count, 0, ',', '.') }}</td>
                            <td><strong>{{ number_format($snapshot->total_count, 0, ',', '.') }}</strong></td>
                            <td>{{ $snapshot->reference_date?->format('d-m-Y') ?? '—' }}</td>
                            <td>{{ $snapshot->source ?: 'Belum diisi' }}</td>
                            <td>
                                <span class="label {{ $snapshot->is_published ? 'label-success' : 'label-default' }}">
                                    {{ $snapshot->is_published ? 'Publik' : 'Draf' }}
                                </span>
                            </td>
                            <td class="table-actions">
                                <a href="{{ route('admin.population.yearly-snapshots.edit', $snapshot) }}" class="btn btn-xs btn-warning" aria-label="Ubah snapshot {{ $snapshot->year }}">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="post" action="{{ route('admin.population.yearly-snapshots.toggle-publication', $snapshot) }}">
                                    @csrf @method('patch')
                                    <button class="btn btn-xs {{ $snapshot->is_published ? 'btn-default' : 'btn-success' }}" aria-label="{{ $snapshot->is_published ? 'Sembunyikan' : 'Publikasikan' }} snapshot {{ $snapshot->year }}">
                                        <i class="fa {{ $snapshot->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                <form method="post" action="{{ route('admin.population.yearly-snapshots.destroy', $snapshot) }}"
                                      data-confirm="Hapus snapshot tahun {{ $snapshot->year }}?"
                                      data-confirm-tone="danger">
                                    @csrf @method('delete')
                                    <button class="btn btn-xs btn-danger" aria-label="Hapus snapshot {{ $snapshot->year }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fa fa-line-chart"></i><br>
                                Belum ada snapshot statistik tahunan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $snapshots->links() }}
    </div>
</div>
@endsection
