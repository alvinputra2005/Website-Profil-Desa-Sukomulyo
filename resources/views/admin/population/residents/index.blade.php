@extends('layouts.admin')
@section('title','Penduduk')
@section('page-description','Data induk penduduk desa')
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-users"></i> Daftar Penduduk</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-social btn-success btn-sm" data-toggle="modal" data-target="#import-residents"><i class="fa fa-file-excel-o"></i> Impor Excel</button>
            <a href="{{ route('admin.population.residents.create') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Penduduk</a>
        </div>
    </div>
    <div class="box-body">
        <form method="get" class="population-filters">
            <div class="form-group"><label>Cari NIK / Nama</label><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Masukkan NIK atau nama"></div>
            <div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="">Semua status</option>@foreach(['active'=>'Aktif','moved'=>'Pindah','deceased'=>'Meninggal','missing'=>'Hilang'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="form-group"><label>Jenis Kelamin</label><select name="sex" class="form-control"><option value="">Semua</option><option value="L" @selected(request('sex')==='L')>Laki-laki</option><option value="P" @selected(request('sex')==='P')>Perempuan</option></select></div>
            <div class="form-group filter-action"><button class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button><a href="{{ route('admin.population.residents.index') }}" class="btn btn-default">Reset</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover population-table">
                <thead><tr><th>No</th><th>Aksi</th><th>NIK</th><th>Nama</th><th>No. KK</th><th>No. Rumah Tangga</th><th>JK</th><th>Umur</th><th>Alamat/Wilayah</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($residents as $resident)
                    <tr>
                        <td>{{ $residents->firstItem()+$loop->index }}</td>
                        <td class="table-actions">
                            <a href="{{ route('admin.population.residents.show',$resident) }}" class="btn btn-xs btn-info" title="Rincian"><i class="fa fa-eye"></i></a>
                            <a href="{{ route('admin.population.residents.edit',$resident) }}" class="btn btn-xs btn-warning" title="Ubah"><i class="fa fa-edit"></i></a>
                            <form method="post" action="{{ route('admin.population.residents.destroy',$resident) }}" data-confirm="Arsipkan data penduduk ini?">@csrf @method('delete')<button class="btn btn-xs btn-danger" title="Arsipkan"><i class="fa fa-archive"></i></button></form>
                        </td>
                        <td><code>{{ $resident->nik }}</code></td>
                        <td><a href="{{ route('admin.population.residents.show',$resident) }}"><strong>{{ $resident->name }}</strong></a><br><small>{{ $resident->family_relationship ?: 'Hubungan keluarga belum diisi' }}</small></td>
                        <td>{{ $resident->family?->family_card_number ?? '—' }}</td>
                        <td>{{ $resident->household?->household_number ?? '—' }}</td>
                        <td>{{ $resident->sex }}</td>
                        <td>{{ $resident->age !== null ? $resident->age.' th' : '—' }}</td>
                        <td>{{ $resident->current_address ?: '—' }}<br><small>{{ $resident->area?->label ?? 'Wilayah belum diisi' }}</small></td>
                        <td><span class="label {{ $resident->status==='active'?'label-success':'label-default' }}">{{ ['active'=>'Aktif','moved'=>'Pindah','deceased'=>'Meninggal','missing'=>'Hilang'][$resident->status] ?? $resident->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="empty-state"><i class="fa fa-users"></i><br>Belum ada data penduduk.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $residents->links() }}
    </div>
</div>
<div class="modal fade" id="import-residents" tabindex="-1" role="dialog" aria-labelledby="import-residents-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.population.residents.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="import-residents-title"><i class="fa fa-file-excel-o"></i> Impor Data Penduduk</h4>
                </div>
                <div class="modal-body">
                    <p>Unggah file Excel/CSV maksimal 10 MB. NIK baru akan ditambahkan; NIK yang sudah ada akan diperbarui. Baris tidak valid dilewati.</p>
                    <div class="form-group">
                        <label class="required" for="resident-import-file">File data</label>
                        <input id="resident-import-file" type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <a href="{{ route('admin.population.residents.import-template') }}" class="btn btn-default" data-no-ajax><i class="fa fa-download"></i> Unduh Template Excel</a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button class="btn btn-success"><i class="fa fa-upload"></i> Impor Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@if(session('import_errors'))
<div class="box box-warning">
    <div class="box-header with-border"><h3 class="box-title">Baris yang gagal diimpor</h3></div>
    <div class="box-body"><ul>@foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul></div>
</div>
@endif
@endsection
