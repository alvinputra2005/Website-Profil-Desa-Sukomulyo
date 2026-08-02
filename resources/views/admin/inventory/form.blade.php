@extends('layouts.admin')
@section('title',($item->exists?'Ubah':'Tambah').' Inventaris '.$categoryData['label'])
@section('page-description',$categoryData['description'])
@section('content')
@include('admin.inventory.partials.menu')
<form method="post" action="{{ $item->exists?route('admin.inventory.update',[$category,$item]):route('admin.inventory.store',$category) }}" data-dirty-form>
    @csrf @if($item->exists)@method('put')@endif
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa {{ $categoryData['icon'] }}"></i> Data Utama</h3></div>
        <div class="box-body"><div class="row">
            <div class="col-md-6">
                <div class="form-group"><label class="required">Nama Barang</label><input name="name" class="form-control" maxlength="255" required value="{{ old('name',$item->name) }}"></div>
                <div class="row"><div class="col-sm-6"><div class="form-group"><label class="required">Kode Barang</label><input name="item_code" class="form-control" maxlength="64" required value="{{ old('item_code',$item->item_code) }}"></div></div><div class="col-sm-6"><div class="form-group"><label class="required">Nomor Register</label><input name="register_number" class="form-control" maxlength="64" required value="{{ old('register_number',$item->register_number) }}"></div></div></div>
                <div class="row"><div class="col-sm-6"><div class="form-group"><label class="required">Tahun Pengadaan</label><input type="number" name="acquisition_year" class="form-control" min="1900" max="{{ now()->year+1 }}" required value="{{ old('acquisition_year',$item->acquisition_year??now()->year) }}"></div></div><div class="col-sm-6"><div class="form-group"><label class="required">Jumlah</label><input type="number" name="quantity" class="form-control" min="1" required value="{{ old('quantity',$item->quantity??1) }}"></div></div></div>
                <div class="form-group"><label class="required">Asal-usul</label><select name="origin" class="form-control" required><option value="">Pilih asal-usul</option>@foreach(\App\Support\InventoryCategory::ORIGINS as $origin)<option value="{{ $origin }}" @selected(old('origin',$item->origin)===$origin)>{{ $origin }}</option>@endforeach</select></div>
                <div class="row"><div class="col-sm-6"><div class="form-group"><label class="required">Harga / Nilai (Rp)</label><input type="number" name="value" class="form-control" min="0" step="0.01" required value="{{ old('value',$item->value??0) }}"></div></div><div class="col-sm-6"><div class="form-group"><label class="required">Kondisi</label><select name="condition" class="form-control" required>@foreach(\App\Support\InventoryCategory::CONDITIONS as $condition)<option @selected(old('condition',$item->condition??'Baik')===$condition)>{{ $condition }}</option>@endforeach</select></div></div></div>
            </div>
            <div class="col-md-6">
                <h4>Rincian {{ $categoryData['label'] }}</h4>
                @foreach($categoryData['fields'] as $key => [$label,$type,$required])
                    @php($value=old('details.'.$key,data_get($item->details,$key)))
                    <div class="form-group"><label class="{{ $required?'required':'' }}">{{ $label }}</label>
                    @if(str_starts_with($type,'select:'))
                        <select name="details[{{ $key }}]" class="form-control" @required($required)><option value="">Pilih</option>@foreach(explode(',',substr($type,7)) as $option)<option value="{{ $option }}" @selected($value===$option)>{{ $option }}</option>@endforeach</select>
                    @else<input type="{{ $type }}" name="details[{{ $key }}]" class="form-control" @if($type==='number') min="0" step="any" @endif @required($required) value="{{ $value }}">@endif
                    </div>
                @endforeach
                <div class="form-group"><label>Keterangan</label><textarea name="notes" class="form-control" rows="4" maxlength="10000">{{ old('notes',$item->notes) }}</textarea></div>
            </div>
        </div></div>
        <div class="box-footer"><a href="{{ route('admin.inventory.index',$category) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Inventaris</button></div>
    </div>
</form>
@endsection
