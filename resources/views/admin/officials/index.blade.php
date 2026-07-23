@extends('layouts.admin')
@section('title', 'Struktur Pemerintahan')
@section('page-description', 'Kelola perangkat dan struktur organisasi pemerintah desa')

@section('content')
<div class="box box-info official-list-box">
    <div class="box-header with-border">
        <div class="official-toolbar">
            <a href="{{ route('admin.officials.create') }}" class="btn btn-social btn-info btn-sm">
                <i class="fa fa-plus"></i> Tambah Perangkat Desa
            </a>
            <button type="submit" form="bulk-delete-officials" class="btn btn-social bg-maroon btn-sm" data-bulk-delete disabled>
                <i class="fa fa-trash"></i> Hapus Terpilih
            </button>
            <a href="{{ route('admin.officials.organization') }}" class="btn btn-social bg-olive btn-sm">
                <i class="fa fa-sitemap"></i> Bagan Organisasi
            </a>
            <a href="{{ route('admin.officials.print') }}" target="_blank" class="btn btn-social bg-purple btn-sm">
                <i class="fa fa-print"></i> Cetak
            </a>
            <a href="{{ route('admin.officials.export') }}" class="btn btn-social bg-navy btn-sm">
                <i class="fa fa-download"></i> Unduh CSV
            </a>
        </div>
    </div>

    <div class="box-body">
        <form method="get" class="official-filters">
            <div class="form-group">
                <label for="official-q">Cari Staf</label>
                <input id="official-q" name="q" class="form-control input-sm" value="{{ request('q') }}" placeholder="Nama, NIK, NIP, NIPD, jabatan, atau tag ID">
            </div>
            <div class="form-group">
                <label for="official-status">Status Pegawai</label>
                <select id="official-status" name="status" class="form-control input-sm select2">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label for="official-position">Jabatan</label>
                <select id="official-position" name="position" class="form-control input-sm select2">
                    <option value="">Semua Jabatan</option>
                    @foreach($positions as $position)
                        <option value="{{ $position }}" @selected(request('position') === $position)>{{ $position }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-action">
                <button class="btn btn-info btn-sm"><i class="fa fa-filter"></i> Tampilkan</button>
                <a href="{{ route('admin.officials.index') }}" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
            </div>
        </form>

        <form id="bulk-delete-officials" method="post" action="{{ route('admin.officials.bulk-destroy') }}" data-confirm="Hapus seluruh data perangkat desa yang dipilih?">
            @csrf
            @method('delete')
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover official-table">
                <thead>
                    <tr>
                        <th class="official-order-column"><i class="fa fa-sort"></i></th>
                        <th class="official-check-column"><input type="checkbox" data-check-all-officials aria-label="Pilih semua"></th>
                        <th class="official-number-column">No</th>
                        <th class="official-actions-column">Aksi</th>
                        <th>Foto</th>
                        <th>Nama, NIP/NIPD, NIK, Tag ID Card</th>
                        <th>Tempat, Tanggal Lahir</th>
                        <th>Jenis Kelamin</th>
                        <th>Agama</th>
                        <th>Pangkat / Golongan</th>
                        <th>Jabatan</th>
                        <th>Pendidikan Terakhir</th>
                        <th>Nomor SK Pengangkatan</th>
                        <th>Tanggal SK Pengangkatan</th>
                        <th>Nomor SK Pemberhentian</th>
                        <th>Tanggal SK Pemberhentian</th>
                        <th>Masa / Periode Jabatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($officials as $official)
                        <tr>
                            <td class="official-order-actions">
                                <form method="post" action="{{ route('admin.officials.move', [$official, 'up']) }}">@csrf @method('patch')<button class="btn btn-link btn-xs" title="Naik"><i class="fa fa-chevron-up"></i></button></form>
                                <form method="post" action="{{ route('admin.officials.move', [$official, 'down']) }}">@csrf @method('patch')<button class="btn btn-link btn-xs" title="Turun"><i class="fa fa-chevron-down"></i></button></form>
                            </td>
                            <td><input type="checkbox" name="ids[]" value="{{ $official->id }}" form="bulk-delete-officials" data-official-check aria-label="Pilih {{ $official->name }}"></td>
                            <td>{{ $officials->firstItem() + $loop->index }}</td>
                            <td class="table-actions official-row-actions">
                                <a href="{{ route('admin.officials.edit', $official) }}" class="btn bg-orange btn-xs" title="Ubah"><i class="fa fa-edit"></i></a>
                                <form method="post" action="{{ route('admin.officials.toggle-status', $official) }}" data-confirm="Ubah status pegawai ini?">
                                    @csrf @method('patch')
                                    <button class="btn {{ $official->is_active ? 'btn-success' : 'btn-default' }} btn-xs" title="{{ $official->is_active ? 'Nonaktifkan pegawai' : 'Aktifkan pegawai' }}"><i class="fa {{ $official->is_active ? 'fa-unlock' : 'fa-lock' }}"></i></button>
                                </form>
                                <form method="post" action="{{ route('admin.officials.destroy', $official) }}" data-confirm="Hapus data {{ $official->full_name }}?">
                                    @csrf @method('delete')
                                    <button class="btn bg-maroon btn-xs" title="Hapus"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                            <td class="text-center">
                                @if($official->photo)
                                    <img src="{{ $official->photo->thumbnail_url }}" class="official-list-photo" alt="{{ $official->photo->alt_text ?: $official->name }}">
                                @else
                                    <span class="official-list-avatar">{{ strtoupper(substr($official->name, 0, 1)) }}</span>
                                @endif
                            </td>
                            <td class="official-identity">
                                <strong>{{ $official->full_name }}</strong>
                                @if($official->resident_id)<span class="label label-info">Data Penduduk</span>@endif
                                <p>
                                    NIP: {{ $official->nip ?: '-' }}<br>
                                    NIPD: {{ $official->village_employee_number ?: '-' }}<br>
                                    NIK: {{ $official->nik ?: '-' }}<br>
                                    Tag ID Card: {{ $official->id_card_tag ?: '-' }}
                                </p>
                            </td>
                            <td>{{ $official->birth_place ?: '-' }}{{ $official->birth_date ? ', '.$official->birth_date->translatedFormat('d F Y') : '' }}</td>
                            <td>{{ $official->sex_label }}</td>
                            <td>{{ $official->religion ?: '-' }}</td>
                            <td>{{ $official->rank_grade ?: '-' }}</td>
                            <td>
                                <strong>{{ $official->position_label }}</strong><br>
                                <x-admin.badge :value="$official->is_active"/>
                            </td>
                            <td>{{ $official->education ?: '-' }}</td>
                            <td>{{ $official->appointment_decree ?: '-' }}</td>
                            <td>{{ $official->appointment_date?->translatedFormat('d F Y') ?: '-' }}</td>
                            <td>{{ $official->dismissal_decree ?: '-' }}</td>
                            <td>{{ $official->dismissal_date?->translatedFormat('d F Y') ?: '-' }}</td>
                            <td>{{ $official->term ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="empty-state">
                                <i class="fa fa-users fa-3x"></i>
                                <h4>Belum ada data perangkat desa</h4>
                                <p>Tambahkan staf dari database penduduk atau masukkan data staf yang belum terdata.</p>
                                <a href="{{ route('admin.officials.create') }}" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Perangkat Desa</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">
        <span class="text-muted">Menampilkan {{ $officials->firstItem() ?: 0 }}–{{ $officials->lastItem() ?: 0 }} dari {{ $officials->total() }} staf</span>
        <div class="pull-right">{{ $officials->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const all = document.querySelector('[data-check-all-officials]');
    const checks = Array.from(document.querySelectorAll('[data-official-check]'));
    const bulkButton = document.querySelector('[data-bulk-delete]');
    const update = function () {
        const checked = checks.filter(function (checkbox) { return checkbox.checked; }).length;
        bulkButton.disabled = checked === 0;
        all.checked = checks.length > 0 && checked === checks.length;
        all.indeterminate = checked > 0 && checked < checks.length;
    };
    all?.addEventListener('change', function () {
        checks.forEach(function (checkbox) { checkbox.checked = all.checked; });
        update();
    });
    checks.forEach(function (checkbox) { checkbox.addEventListener('change', update); });
    update();
});
</script>
@endpush
