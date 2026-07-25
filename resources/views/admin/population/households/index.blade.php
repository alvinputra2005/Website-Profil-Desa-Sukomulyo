@extends('layouts.admin')
@section('title','Rumah Tangga')
@section('page-description','Pengelompokan keluarga dalam satu rumah tangga')
@section('content')
<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-home"></i> Daftar Rumah Tangga</h3><div class="box-tools"><a href="{{ route('admin.population.households.create') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Rumah Tangga</a></div></div>
    <div class="box-body">
        <form method="get" class="population-filters population-filters-compact"><div class="form-group"><label>Cari Nomor / Kepala Rumah Tangga</label><input name="q" class="form-control" value="{{ request('q') }}"></div><div class="form-group filter-action"><button class="btn btn-primary"><i class="fa fa-search"></i> Cari</button><a href="{{ route('admin.population.households.index') }}" class="btn btn-default">Reset</a></div></form>
        <div class="table-responsive"><table class="table table-striped table-hover population-table">
            <thead><tr><th>No</th><th>Aksi</th><th>Nomor Rumah Tangga</th><th>Kepala Rumah Tangga</th><th>NIK</th><th>Jumlah KK</th><th>Anggota</th><th>Alamat/Wilayah</th><th>DTKS</th></tr></thead>
            <tbody>@forelse($households as $household)<tr>
                <td>{{ $households->firstItem()+$loop->index }}</td>
                <td class="table-actions"><a href="{{ route('admin.population.households.edit',$household) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a><form method="post" action="{{ route('admin.population.households.destroy',$household) }}" data-confirm="Arsipkan rumah tangga ini?">@csrf @method('delete')<button class="btn btn-xs btn-danger"><i class="fa fa-archive"></i></button></form></td>
                <td><strong>{{ $household->household_number }}</strong></td><td>{{ $household->head?->name ?? 'Belum ditetapkan' }}</td><td>{{ $household->head?->nik ?? '—' }}</td>
                <td>{{ $household->members->pluck('family_id')->filter()->unique()->count() }}</td><td>{{ $household->members_count }} orang</td>
                <td>{{ $household->address ?: '—' }}<br><small>{{ $household->area?->label ?? 'Wilayah belum diisi' }}</small></td>
                <td><span class="label {{ $household->is_dtks_registered?'label-success':'label-default' }}">{{ $household->is_dtks_registered?'Terdaftar':'Tidak' }}</span></td>
            </tr>@empty<tr><td colspan="9" class="empty-state"><i class="fa fa-home"></i><br>Belum ada rumah tangga.</td></tr>@endforelse</tbody>
        </table></div>
        {{ $households->links() }}
    </div>
</div>
@endsection
