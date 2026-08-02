@extends('layouts.admin')
@section('title','Keluarga')
@section('page-description','Pengelompokan penduduk berdasarkan Kartu Keluarga')
@section('content')
<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-card"></i> Daftar Keluarga</h3><div class="box-tools population-header-actions"><a href="{{ route('admin.population.families.create') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Keluarga</a><form id="bulk-delete-families" method="post" action="{{ route('admin.population.families.bulk-destroy') }}" class="inline-form" data-confirm="Arsipkan keluarga yang dipilih?" data-confirm-tone="danger" data-confirm-title="Arsipkan Keluarga Terpilih" data-confirm-button="Ya, arsipkan">@csrf @method('delete')<button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-archive"></i> Arsipkan Terpilih</button></form><a href="{{ route('admin.population.families.archive') }}" class="btn btn-default btn-sm"><i class="fa fa-archive"></i> Arsip</a></div></div>
    <div class="box-body">
        <form method="get" class="population-filters population-filters-compact">
            <div class="form-group"><label>Cari No. KK / Kepala Keluarga</label><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Nomor KK, NIK, atau nama"></div>
            <div class="form-group"><label>Status</label><select name="is_active" class="form-control"><option value="">Semua</option><option value="1" @selected(request('is_active')==='1')>Aktif</option><option value="0" @selected(request('is_active')==='0')>Tidak aktif</option></select></div>
            <div class="form-group filter-action"><button class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button><a href="{{ route('admin.population.families.index') }}" class="btn btn-default">Reset</a></div>
        </form>
        <div class="table-responsive"><table class="table table-striped table-hover population-table">
            <thead><tr><th style="width:38px"><input type="checkbox" aria-label="Pilih semua keluarga pada halaman ini" data-bulk-select-all></th><th>No</th><th>Aksi</th><th>Nomor KK</th><th>Kepala Keluarga</th><th>NIK</th><th>Anggota</th><th>Alamat/Wilayah</th><th>Terdaftar</th><th>Status</th></tr></thead>
            <tbody>@forelse($families as $family)<tr>
                <td><input type="checkbox" name="ids[]" value="{{ $family->id }}" form="bulk-delete-families" aria-label="Pilih keluarga {{ $family->family_card_number }}" data-bulk-item></td>
                <td>{{ $families->firstItem()+$loop->index }}</td>
                <td class="table-actions"><a href="{{ route('admin.population.families.edit',$family) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a><form method="post" action="{{ route('admin.population.families.destroy',$family) }}" data-confirm="Arsipkan keluarga ini?">@csrf @method('delete')<button class="btn btn-xs btn-danger"><i class="fa fa-archive"></i></button></form></td>
                <td><strong>{{ $family->family_card_number }}</strong></td>
                <td>{{ $family->head?->name ?? 'Belum ditetapkan' }}</td>
                <td>{{ $family->head?->nik ?? '—' }}</td>
                <td><span class="badge">{{ $family->members_count }}</span> orang</td>
                <td>{{ $family->address ?: '—' }}<br><small>{{ $family->area?->label ?? 'Wilayah belum diisi' }}</small></td>
                <td>{{ $family->registered_at?->format('d-m-Y') ?: '—' }}</td>
                <td><span class="label {{ $family->is_active?'label-success':'label-default' }}">{{ $family->is_active?'Aktif':'Tidak aktif' }}</span></td>
            </tr>@empty<tr><td colspan="10" class="empty-state"><i class="fa fa-id-card"></i><br>Belum ada data keluarga.</td></tr>@endforelse</tbody>
        </table></div>
        {{ $families->links() }}
    </div>
</div>
@endsection
