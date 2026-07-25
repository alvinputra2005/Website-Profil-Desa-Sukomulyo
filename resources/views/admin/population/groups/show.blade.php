@extends('layouts.admin')
@section('title','Anggota Kelompok')
@section('page-description',$group->name)
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Informasi Kelompok</h3></div><div class="box-body">
            <h3>{{ $group->name }}</h3><p class="text-muted">{{ $group->category }}</p>
            <dl><dt>Kode</dt><dd>{{ $group->code }}</dd><dt>Ketua</dt><dd>{{ $group->chairperson?->name ?? '—' }}</dd><dt>SK Pendirian</dt><dd>{{ $group->establishment_decree ?: '—' }}</dd><dt>Deskripsi</dt><dd>{{ $group->description ?: '—' }}</dd></dl>
        </div><div class="box-footer"><a href="{{ route('admin.population.groups.edit',$group) }}" class="btn btn-warning"><i class="fa fa-edit"></i> Ubah Kelompok</a></div></div>
    </div>
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users"></i> Daftar Anggota</h3><div class="box-tools"><a href="{{ route('admin.population.groups.members.create',$group) }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-user-plus"></i> Tambah Anggota</a></div></div>
            <div class="box-body no-padding"><div class="table-responsive"><table class="table table-striped">
                <thead><tr><th>No</th><th>Aksi</th><th>No. Anggota</th><th>NIK</th><th>Nama</th><th>Jabatan</th><th>Periode</th></tr></thead>
                <tbody>@forelse($group->memberships->sortBy(fn($member)=>$member->position==='Ketua'?0:1) as $membership)<tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="table-actions"><a href="{{ route('admin.population.groups.members.edit',[$group,$membership]) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a><form method="post" action="{{ route('admin.population.groups.members.destroy',[$group,$membership]) }}" data-confirm="Hapus anggota dari kelompok?">@csrf @method('delete')<button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button></form></td>
                    <td>{{ $membership->member_number ?: '—' }}</td><td>{{ $membership->resident->nik }}</td><td><a href="{{ route('admin.population.residents.show',$membership->resident) }}">{{ $membership->resident->name }}</a></td><td><span class="label {{ $membership->position==='Ketua'?'label-success':'label-default' }}">{{ $membership->position }}</span></td><td>{{ $membership->period ?: '—' }}</td>
                </tr>@empty<tr><td colspan="7" class="empty-state">Belum ada anggota kelompok.</td></tr>@endforelse</tbody>
            </table></div></div>
            <div class="box-footer"><a href="{{ route('admin.population.groups.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Daftar Kelompok</a></div>
        </div>
    </div>
</div>
@endsection
