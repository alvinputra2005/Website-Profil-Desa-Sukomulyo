@extends('layouts.admin')
@section('title','Laporan Inventaris Desa')
@section('page-description','Rekap keseluruhan aset dan kekayaan Desa Sukomulyo')
@section('content')
@include('admin.inventory.partials.menu')
<div class="row">
    <div class="col-sm-3"><div class="small-box bg-aqua"><div class="inner"><h3>{{ number_format($summary['count'],0,',','.') }}</h3><p>Register Aset</p></div><div class="icon"><i class="fa fa-archive"></i></div></div></div>
    <div class="col-sm-3"><div class="small-box bg-green"><div class="inner"><h3>{{ number_format($summary['quantity'],0,',','.') }}</h3><p>Total Unit</p></div><div class="icon"><i class="fa fa-cubes"></i></div></div></div>
    <div class="col-sm-3"><div class="small-box bg-yellow"><div class="inner"><h3>{{ number_format($summary['active'],0,',','.') }}</h3><p>Aset Aktif</p></div><div class="icon"><i class="fa fa-check-circle"></i></div></div></div>
    <div class="col-sm-3"><div class="small-box bg-red"><div class="inner"><h3>{{ number_format($mutationCount,0,',','.') }}</h3><p>Catatan Mutasi</p></div><div class="icon"><i class="fa fa-exchange"></i></div></div></div>
</div>
<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-filter"></i> Filter Laporan</h3><div class="box-tools"><a target="_blank" href="{{ route('admin.inventory.report.print',request()->query()) }}" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Cetak</a> <a href="{{ route('admin.inventory.report.csv',request()->query()) }}" class="btn btn-success btn-sm"><i class="fa fa-download"></i> CSV</a></div></div>
    <div class="box-body">
        <form method="get"><div class="row">
            <div class="col-md-3"><div class="form-group"><label>Kategori</label><select name="category" class="form-control"><option value="">Semua kategori</option>@foreach(\App\Support\InventoryCategory::all() as $slug=>$data)<option value="{{ $slug }}" @selected(request('category')===$slug)>{{ $data['label'] }}</option>@endforeach</select></div></div>
            <div class="col-md-2"><div class="form-group"><label>Tahun</label><select name="year" class="form-control"><option value="">Semua tahun</option>@foreach($years as $year)<option value="{{ $year }}" @selected((string)request('year')===(string)$year)>{{ $year }}</option>@endforeach</select></div></div>
            <div class="col-md-3"><div class="form-group"><label>Asal-usul</label><select name="origin" class="form-control"><option value="">Semua asal</option>@foreach(\App\Support\InventoryCategory::ORIGINS as $origin)<option value="{{ $origin }}" @selected(request('origin')===$origin)>{{ $origin }}</option>@endforeach</select></div></div>
            <div class="col-md-2"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="">Semua status</option><option @selected(request('status')==='Aktif')>Aktif</option><option @selected(request('status')==='Dimutasi')>Dimutasi</option></select></div></div>
            <div class="col-md-2"><div class="form-group"><label>&nbsp;</label><button class="btn btn-info btn-block"><i class="fa fa-search"></i> Tampilkan</button></div></div>
        </div><div class="form-group"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, kode barang, atau nomor register"></div></form>
        <div class="callout callout-info"><h4>Total nilai inventaris: Rp{{ number_format($summary['value'],2,',','.') }}</h4><p>Nilai merupakan penjumlahan harga/nilai per register sesuai filter aktif.</p></div>
        <div class="row">@foreach($summary['by_category'] as $group)<div class="col-md-4 col-sm-6"><div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-folder-open"></i></span><div class="info-box-content"><span class="info-box-text">{{ $group['label'] }}</span><span class="info-box-number">{{ $group['count'] }} register</span><span>Rp{{ number_format($group['value'],0,',','.') }}</span></div></div></div>@endforeach</div>
        <div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th>No.</th><th>Kategori</th><th>Nama / Kode</th><th>Register</th><th>Tahun</th><th>Asal-usul</th><th>Jumlah</th><th>Kondisi</th><th>Status</th><th>Nilai</th></tr></thead><tbody>
            @forelse($items as $item)<tr><td>{{ $items->firstItem()+$loop->index }}</td><td>{{ \App\Support\InventoryCategory::label($item->category) }}</td><td><a href="{{ route('admin.inventory.show',[$item->category,$item]) }}"><strong>{{ $item->name }}</strong></a><br><small>{{ $item->item_code }}</small></td><td>{{ $item->register_number }}</td><td>{{ $item->acquisition_year }}</td><td>{{ $item->origin }}</td><td>{{ $item->quantity }}</td><td>{{ $item->condition }}</td><td>{{ $item->status }}</td><td>Rp{{ number_format((float)$item->value,0,',','.') }}</td></tr>
            @empty<tr><td colspan="10" class="empty-state"><i class="fa fa-archive"></i><br>Tidak ada data sesuai filter.</td></tr>@endforelse
        </tbody><tfoot><tr><th colspan="9" class="text-right">Total Nilai</th><th>Rp{{ number_format($summary['value'],0,',','.') }}</th></tr></tfoot></table></div>
        {{ $items->links() }}
    </div>
</div>
@endsection
