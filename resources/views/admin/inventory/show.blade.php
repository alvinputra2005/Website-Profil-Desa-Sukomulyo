@extends('layouts.admin')
@section('title','Rincian Inventaris '.$categoryData['label'])
@section('page-description',$item->name)
@section('content')
@include('admin.inventory.partials.menu')
<div class="row">
    <div class="col-md-5">
        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa {{ $categoryData['icon'] }}"></i> {{ $item->name }}</h3><div class="box-tools"><a href="{{ route('admin.inventory.edit',[$category,$item]) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Ubah</a></div></div>
            <div class="box-body no-padding"><table class="table table-striped">
                <tr><th style="width:40%">Kategori</th><td>{{ $categoryData['label'] }}</td></tr><tr><th>Kode Barang</th><td>{{ $item->item_code }}</td></tr><tr><th>Nomor Register</th><td>{{ $item->register_number }}</td></tr>
                <tr><th>Tahun Pengadaan</th><td>{{ $item->acquisition_year }}</td></tr><tr><th>Asal-usul</th><td>{{ $item->origin }}</td></tr><tr><th>Jumlah</th><td>{{ number_format($item->quantity,0,',','.') }}</td></tr>
                <tr><th>Nilai</th><td>Rp{{ number_format((float)$item->value,2,',','.') }}</td></tr><tr><th>Kondisi</th><td>{{ $item->condition }}</td></tr><tr><th>Status</th><td><span class="label {{ $item->status==='Aktif'?'label-success':'label-default' }}">{{ $item->status }}</span></td></tr>
                @foreach($categoryData['fields'] as $key => [$label]) @if(filled(data_get($item->details,$key)))<tr><th>{{ $label }}</th><td>{{ data_get($item->details,$key) }}</td></tr>@endif @endforeach
                <tr><th>Keterangan</th><td>{!! nl2br(e($item->notes ?: '---')) !!}</td></tr><tr><th>Diperbarui</th><td>{{ $item->updated_at?->format('d-m-Y H:i') }} @if($item->updatedBy)<small>oleh {{ $item->updatedBy->name }}</small>@endif</td></tr>
            </table></div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exchange"></i> Riwayat Mutasi</h3>
                <div class="box-tools admin-list-actions">
                    <a href="{{ route('admin.inventory.mutations.create',[$category,$item]) }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Catat Mutasi</a>
                    <form id="bulk-delete-inventory-mutations" method="post" action="{{ route('admin.inventory.mutations.bulk-destroy',[$category,$item]) }}" class="inline-form" data-confirm="Hapus catatan mutasi yang dipilih?" data-confirm-tone="danger" data-confirm-title="Hapus Mutasi Terpilih" data-confirm-button="Ya, hapus">
                        @csrf
                        @method('delete')
                        <button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
                    </form>
                </div>
            </div>
            <div class="box-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th style="width:38px"><input type="checkbox" aria-label="Pilih semua mutasi pada halaman ini" data-bulk-select-all></th><th>Tanggal</th><th>Status / Jenis</th><th>Nilai / Penerima</th><th>Keterangan</th><th>Aksi</th></tr></thead><tbody>
                @forelse($item->mutations as $mutation)<tr><td><input type="checkbox" name="ids[]" value="{{ $mutation->id }}" form="bulk-delete-inventory-mutations" aria-label="Pilih mutasi {{ $mutation->mutation_date->format('d-m-Y') }}" data-bulk-item></td><td>{{ $mutation->mutation_date->format('d-m-Y') }}</td><td><strong>{{ $mutation->asset_status }}</strong><br><small>{{ $mutation->mutation_type }}</small></td><td>@if($mutation->sale_price)Rp{{ number_format((float)$mutation->sale_price,0,',','.') }}@endif @if($mutation->recipient)<br>{{ $mutation->recipient }}@endif @if(!$mutation->sale_price&&!$mutation->recipient)---@endif</td><td>{{ $mutation->notes }}</td><td class="table-actions"><a href="{{ route('admin.inventory.mutations.edit',[$category,$item,$mutation]) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a><form method="post" action="{{ route('admin.inventory.mutations.destroy',[$category,$item,$mutation]) }}" data-confirm="Hapus catatan mutasi ini?" data-confirm-tone="danger">@csrf @method('delete')<button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button></form></td></tr>
                @empty<tr><td colspan="6" class="text-center text-muted">Belum ada catatan mutasi.</td></tr>@endforelse
            </tbody></table></div></div>
        </div>
    </div>
</div>
<a href="{{ route('admin.inventory.index',$category) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>
@endsection
