@extends('layouts.admin')
@section('title',($mutation->exists?'Ubah':'Tambah').' Mutasi Inventaris')
@section('page-description',$item->name.' · '.$item->item_code.' / '.$item->register_number)
@section('content')
@include('admin.inventory.partials.menu')
<form method="post" action="{{ $mutation->exists?route('admin.inventory.mutations.update',[$category,$item,$mutation]):route('admin.inventory.mutations.store',[$category,$item]) }}" data-dirty-form>
    @csrf @if($mutation->exists)@method('put')@endif
    <div class="row"><div class="col-md-8 col-md-offset-2"><div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exchange"></i> Mutasi: {{ $item->name }}</h3></div>
        <div class="box-body">
            <div class="callout callout-info"><strong>{{ $categoryData['label'] }}</strong> — {{ $item->item_code }} / {{ $item->register_number }}, diperoleh tahun {{ $item->acquisition_year }}.</div>
            <div class="row"><div class="col-sm-6"><div class="form-group"><label class="required">Status Aset</label><select name="asset_status" class="form-control" required>@foreach(['Baik','Rusak','Diperbaiki','Hapus'] as $status)<option @selected(old('asset_status',$mutation->asset_status??'Baik')===$status)>{{ $status }}</option>@endforeach</select></div></div><div class="col-sm-6"><div class="form-group"><label class="required">Tanggal Mutasi</label><input type="date" name="mutation_date" class="form-control" max="{{ now()->format('Y-m-d') }}" required value="{{ old('mutation_date',$mutation->mutation_date?->format('Y-m-d')??now()->format('Y-m-d')) }}"></div></div></div>
            <div class="form-group"><label class="required">Jenis Mutasi</label><select name="mutation_type" id="mutation_type" class="form-control" required><option value="">Pilih jenis mutasi</option>@foreach(['Perubahan Status'=>['Status Baik','Status Rusak','Perbaikan'],'Disumbangkan'=>['Masih Baik Disumbangkan','Barang Rusak Disumbangkan'],'Dijual'=>['Masih Baik Dijual','Barang Rusak Dijual'],'Penghapusan Lain'=>['Hibah','Musnah','Hilang']] as $group=>$options)<optgroup label="{{ $group }}">@foreach($options as $option)<option value="{{ $option }}" @selected(old('mutation_type',$mutation->mutation_type)===$option)>{{ $option }}</option>@endforeach</optgroup>@endforeach</select></div>
            <div class="row"><div class="col-sm-6"><div class="form-group"><label>Harga Penjualan (Rp)</label><input type="number" name="sale_price" id="sale_price" min="0" step="0.01" class="form-control" value="{{ old('sale_price',$mutation->sale_price) }}"><p class="help-block">Wajib untuk mutasi penjualan.</p></div></div><div class="col-sm-6"><div class="form-group"><label>Penerima</label><input name="recipient" id="recipient" maxlength="255" class="form-control" value="{{ old('recipient',$mutation->recipient) }}"><p class="help-block">Wajib untuk sumbangan atau hibah.</p></div></div></div>
            <div class="form-group"><label class="required">Keterangan</label><textarea name="notes" rows="4" maxlength="10000" class="form-control" required>{{ old('notes',$mutation->notes) }}</textarea></div>
        </div>
        <div class="box-footer"><a href="{{ route('admin.inventory.show',[$category,$item]) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button class="btn btn-social btn-success pull-right"><i class="fa fa-save"></i> Simpan Mutasi</button></div>
    </div></div></div>
</form>
@endsection
@push('scripts')<script>$(function(){function sync(){var v=$('#mutation_type').val()||'';$('#sale_price').prop('required',v.indexOf('Dijual')!==-1);$('#recipient').prop('required',v.indexOf('Disumbangkan')!==-1||v==='Hibah');}$('#mutation_type').on('change',sync);sync();});</script>@endpush
