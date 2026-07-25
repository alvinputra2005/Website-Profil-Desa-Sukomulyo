@extends('layouts.admin')
@section('title',$household->exists?'Ubah Rumah Tangga':'Tambah Rumah Tangga')
@section('page-description','Data kepala dan anggota rumah tangga')
@section('content')
<form method="post" action="{{ $household->exists?route('admin.population.households.update',$household):route('admin.population.households.store') }}" data-dirty-form>
    @csrf @if($household->exists)@method('put')@endif
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Form Rumah Tangga</h3></div>
        <div class="box-body"><div class="row">
            <div class="col-md-6">
                <div class="form-group"><label class="required">Nomor Rumah Tangga</label><input name="household_number" maxlength="30" class="form-control" value="{{ old('household_number',$household->household_number) }}" required><p class="help-block">Huruf/angka/tanda hubung, wajib mengandung angka.</p></div>
                <div class="form-group"><label class="required">Kepala Rumah Tangga</label><select name="head_resident_id" class="form-control select2" required><option value="">Pilih penduduk</option>@foreach($residents as $resident)<option value="{{ $resident->id }}" @selected(old('head_resident_id',$household->head_resident_id)==$resident->id)>{{ $resident->nik }} — {{ $resident->name }}</option>@endforeach</select></div>
                <div class="form-group"><label>Kelas Sosial</label><input name="social_class" class="form-control" value="{{ old('social_class',$household->social_class) }}"></div>
                <div class="form-group"><label>Tanggal Terdaftar</label><input type="date" name="registered_at" class="form-control" value="{{ old('registered_at',$household->registered_at?->format('Y-m-d')??now()->format('Y-m-d')) }}"></div>
            </div>
            <div class="col-md-6">
                <div class="row"><div class="col-sm-6"><div class="form-group"><label>Dusun</label><input name="hamlet" class="form-control" value="{{ old('hamlet',$household->area?->hamlet) }}"></div></div><div class="col-sm-3"><div class="form-group"><label>RW</label><input name="rw" maxlength="3" class="form-control" value="{{ old('rw',$household->area?->rw) }}"></div></div><div class="col-sm-3"><div class="form-group"><label>RT</label><input name="rt" maxlength="3" class="form-control" value="{{ old('rt',$household->area?->rt) }}"></div></div></div>
                <div class="form-group"><label>Alamat</label><textarea name="address" class="form-control" rows="3">{{ old('address',$household->address) }}</textarea></div>
                <div class="form-group"><label><input type="checkbox" name="is_dtks_registered" value="1" @checked(old('is_dtks_registered',$household->is_dtks_registered))> Terdaftar DTKS</label></div>
                <div class="form-group"><label>Nomor Referensi DTKS</label><input name="dtks_reference" class="form-control" value="{{ old('dtks_reference',$household->dtks_reference) }}"></div>
                <div class="form-group"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$household->exists?$household->is_active:true))> Rumah tangga aktif</label></div>
                @if($household->exists)<div class="callout callout-info"><strong>{{ $household->members->count() }} anggota</strong><p>Keanggotaan rumah tangga dikelola dari formulir penduduk.</p></div>@endif
            </div>
        </div></div>
        <div class="box-footer"><a href="{{ route('admin.population.households.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Rumah Tangga</button></div>
    </div>
</form>
@endsection
