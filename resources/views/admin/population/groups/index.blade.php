@extends('layouts.admin')
@section('title','Kelompok')
@section('page-description','Kelompok kemasyarakatan dan keanggotaannya')
@section('content')
<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-sitemap"></i> Daftar Kelompok</h3><div class="box-tools population-header-actions"><a href="{{ route('admin.population.groups.create') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Kelompok</a><form id="bulk-delete-groups" method="post" action="{{ route('admin.population.groups.bulk-destroy') }}" class="inline-form" data-confirm="Arsipkan kelompok yang dipilih?" data-confirm-tone="danger" data-confirm-title="Arsipkan Kelompok Terpilih" data-confirm-button="Ya, arsipkan">@csrf @method('delete')<button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-archive"></i> Arsipkan Terpilih</button></form><a href="{{ route('admin.population.groups.archive') }}" class="btn btn-default btn-sm"><i class="fa fa-archive"></i> Arsip</a></div></div>
    <div class="box-body">
        <form method="get" class="population-filters population-filters-compact">
            <div class="form-group"><label>Cari Kode / Nama</label><input name="q" class="form-control" value="{{ request('q') }}"></div>
            <div class="form-group"><label>Kategori</label><select name="category" class="form-control select2"><option value="">Semua kategori</option>@foreach($categories as $option)<option @selected(request('category')===$option)>{{ $option }}</option>@endforeach</select></div>
            <div class="form-group filter-action"><button class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button><a href="{{ route('admin.population.groups.index') }}" class="btn btn-default">Reset</a></div>
        </form>
        <div class="table-responsive"><table class="table table-striped table-hover population-table">
            <thead><tr><th style="width:38px"><input type="checkbox" aria-label="Pilih semua kelompok pada halaman ini" data-bulk-select-all></th><th>No</th><th>Aksi</th><th>Kode</th><th>Nama Kelompok</th><th>Ketua</th><th>Kategori</th><th>Anggota</th><th>Status</th></tr></thead>
            <tbody>@forelse($groups as $group)<tr>
                <td><input type="checkbox" name="ids[]" value="{{ $group->id }}" form="bulk-delete-groups" aria-label="Pilih {{ $group->name }}" data-bulk-item></td>
                <td>{{ $groups->firstItem()+$loop->index }}</td>
                <td class="table-actions"><a href="{{ route('admin.population.groups.show',$group) }}" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a><a href="{{ route('admin.population.groups.edit',$group) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a><form method="post" action="{{ route('admin.population.groups.destroy',$group) }}" data-confirm="Arsipkan kelompok ini?">@csrf @method('delete')<button class="btn btn-xs btn-danger"><i class="fa fa-archive"></i></button></form></td>
                <td><code>{{ $group->code }}</code></td><td><a href="{{ route('admin.population.groups.show',$group) }}"><strong>{{ $group->name }}</strong></a></td><td>{{ $group->chairperson?->name ?? 'Belum ditetapkan' }}</td><td>{{ $group->category }}</td><td><span class="badge">{{ $group->memberships_count }}</span></td><td><span class="label {{ $group->is_active?'label-success':'label-default' }}">{{ $group->is_active?'Aktif':'Tidak aktif' }}</span></td>
            </tr>@empty<tr><td colspan="9" class="empty-state"><i class="fa fa-sitemap"></i><br>Belum ada kelompok.</td></tr>@endforelse</tbody>
        </table></div>
        {{ $groups->links() }}
    </div>
</div>
@endsection
