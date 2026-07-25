@extends('layouts.admin')
@section('title',$family->exists?'Ubah Keluarga':'Tambah Keluarga')
@section('page-description','Data Kartu Keluarga dan kepala keluarga')
@section('content')
<form method="post" action="{{ $family->exists?route('admin.population.families.update',$family):route('admin.population.families.store') }}" data-dirty-form>
    @csrf @if($family->exists)@method('put')@endif
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Form Keluarga</h3></div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group"><label class="required">Nomor Kartu Keluarga</label><input name="family_card_number" class="form-control" maxlength="16" inputmode="numeric" value="{{ old('family_card_number',$family->family_card_number) }}" required><p class="help-block">16 digit nomor KK.</p></div>
                    <div class="form-group"><label class="required">Kepala Keluarga</label><select name="head_resident_id" class="form-control select2" required><option value="">Pilih penduduk</option>@foreach($residents as $resident)<option value="{{ $resident->id }}" @selected(old('head_resident_id',$family->head_resident_id)==$resident->id)>{{ $resident->nik }} — {{ $resident->name }}</option>@endforeach</select><p class="help-block">Penduduk terpilih otomatis menjadi anggota dan berstatus Kepala Keluarga.</p></div>
                    <div class="form-group"><label>Kelas Sosial</label><input name="social_class" class="form-control" list="social-class-list" value="{{ old('social_class',$family->social_class) }}"><datalist id="social-class-list"><option>Keluarga Pra-Sejahtera</option><option>Keluarga Sejahtera I</option><option>Keluarga Sejahtera II</option><option>Keluarga Sejahtera III</option></datalist></div>
                    <div class="row"><div class="col-sm-6"><div class="form-group"><label>Tanggal Terdaftar</label><input type="date" name="registered_at" class="form-control" value="{{ old('registered_at',$family->registered_at?->format('Y-m-d')??now()->format('Y-m-d')) }}"></div></div><div class="col-sm-6"><div class="form-group"><label>Tanggal Cetak KK</label><input type="date" name="issued_at" class="form-control" value="{{ old('issued_at',$family->issued_at?->format('Y-m-d')) }}"></div></div></div>
                </div>
                <div class="col-md-6">
                    <div class="row"><div class="col-sm-6"><div class="form-group"><label>Dusun</label><input name="hamlet" class="form-control" value="{{ old('hamlet',$family->area?->hamlet) }}"></div></div><div class="col-sm-3"><div class="form-group"><label>RW</label><input name="rw" maxlength="3" class="form-control" value="{{ old('rw',$family->area?->rw) }}"></div></div><div class="col-sm-3"><div class="form-group"><label>RT</label><input name="rt" maxlength="3" class="form-control" value="{{ old('rt',$family->area?->rt) }}"></div></div></div>
                    <div class="form-group"><label>Alamat</label><textarea name="address" class="form-control" rows="4">{{ old('address',$family->address) }}</textarea></div>
                    <div class="form-group"><label><input name="is_active" value="1" type="checkbox" @checked(old('is_active',$family->exists?$family->is_active:true))> Keluarga aktif</label></div>
                    @if($family->exists)<div class="callout callout-info"><h4>{{ $family->members->count() }} anggota keluarga</h4><p>Anggota dikelola dari formulir masing-masing penduduk. Mengganti kepala keluarga tidak menghapus anggota lama.</p></div>@endif
                </div>
            </div>
        </div>
        <div class="box-footer"><a href="{{ route('admin.population.families.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Keluarga</button></div>
    </div>
</form>
@endsection
