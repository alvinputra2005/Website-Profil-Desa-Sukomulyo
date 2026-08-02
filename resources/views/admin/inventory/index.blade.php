@extends('layouts.admin')
@section('title','Inventaris '.$categoryData['label'])
@section('page-description',$categoryData['description'])
@section('content')
@include('admin.inventory.partials.menu')
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa {{ $categoryData['icon'] }}"></i> Daftar {{ $categoryData['label'] }}</h3>
        <form id="bulk-delete-inventory" method="post" action="{{ route('admin.inventory.bulk-destroy', $category) }}" class="inline-form" data-confirm="Hapus inventaris yang dipilih beserta seluruh riwayat mutasinya?" data-confirm-tone="danger" data-confirm-title="Hapus Inventaris Terpilih" data-confirm-button="Ya, hapus">
            @csrf
            @method('delete')
            <button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
        </form>
        <div class="box-tools"><a href="{{ route('admin.inventory.create',$category) }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Data</a></div>
    </div>
    <div class="box-body">
        <form class="row" method="get">
            <div class="col-md-5"><div class="form-group"><label class="sr-only" for="q">Cari</label><input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari nama, kode, atau nomor register..."></div></div>
            <div class="col-sm-2"><div class="form-group"><select name="year" class="form-control"><option value="">Semua tahun</option>@foreach($years as $year)<option value="{{ $year }}" @selected((string)request('year')===(string)$year)>{{ $year }}</option>@endforeach</select></div></div>
            <div class="col-sm-2"><div class="form-group"><select name="status" class="form-control"><option value="">Semua status</option><option @selected(request('status')==='Aktif')>Aktif</option><option @selected(request('status')==='Dimutasi')>Dimutasi</option></select></div></div>
            <div class="col-sm-3"><button class="btn btn-default"><i class="fa fa-search"></i> Filter</button> <a href="{{ route('admin.inventory.index',$category) }}" class="btn btn-default">Reset</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th style="width:38px"><input type="checkbox" aria-label="Pilih semua inventaris pada halaman ini" data-bulk-select-all></th><th>No.</th><th>Nama / Kode Barang</th><th>Register</th><th>Tahun</th><th>Asal-usul</th><th>Kondisi</th><th>Nilai</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-delete-inventory" aria-label="Pilih {{ $item->name }}" data-bulk-item></td>
                        <td>{{ $items->firstItem()+$loop->index }}</td>
                        <td><strong>{{ $item->name }}</strong><br><small class="text-muted">{{ $item->item_code }} · {{ number_format($item->quantity,0,',','.') }} unit</small></td>
                        <td>{{ $item->register_number }}</td><td>{{ $item->acquisition_year }}</td><td>{{ $item->origin }}</td>
                        <td><span class="label {{ $item->condition==='Baik'?'label-success':($item->condition==='Rusak Ringan'?'label-warning':'label-danger') }}">{{ $item->condition }}</span></td>
                        <td>Rp{{ number_format((float)$item->value,0,',','.') }}</td>
                        <td><span class="label {{ $item->status==='Aktif'?'label-primary':'label-default' }}">{{ $item->status }}</span>@if($item->mutations_count)<br><small>{{ $item->mutations_count }} mutasi</small>@endif</td>
                        <td class="table-actions">
                            <a href="{{ route('admin.inventory.show',[$category,$item]) }}" class="btn btn-xs btn-info" title="Rincian"><i class="fa fa-eye"></i></a>
                            <a href="{{ route('admin.inventory.mutations.create',[$category,$item]) }}" class="btn btn-xs btn-success" title="Catat mutasi"><i class="fa fa-exchange"></i></a>
                            <a href="{{ route('admin.inventory.edit',[$category,$item]) }}" class="btn btn-xs btn-warning" title="Ubah"><i class="fa fa-edit"></i></a>
                            <form method="post" action="{{ route('admin.inventory.destroy',[$category,$item]) }}" data-confirm="Hapus {{ $item->name }} beserta seluruh riwayat mutasinya?" data-confirm-tone="danger">@csrf @method('delete')<button class="btn btn-xs btn-danger" title="Hapus"><i class="fa fa-trash"></i></button></form>
                        </td>
                    </tr>
                @empty<tr><td colspan="10" class="empty-state"><i class="fa {{ $categoryData['icon'] }}"></i><br>Belum ada data {{ strtolower($categoryData['label']) }}.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
</div>
@endsection
