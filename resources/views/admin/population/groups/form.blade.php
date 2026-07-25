@extends('layouts.admin')
@section('title',$group->exists?'Ubah Kelompok':'Tambah Kelompok')
@section('page-description','Identitas dan kepengurusan kelompok')
@section('content')
<form method="post" action="{{ $group->exists?route('admin.population.groups.update',$group):route('admin.population.groups.store') }}" data-dirty-form>
    @csrf @if($group->exists)@method('put')@endif
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Form Kelompok</h3></div>
        <div class="box-body form-horizontal">
            <div class="form-group"><label class="col-sm-3 control-label required">Kode Kelompok</label><div class="col-sm-8"><input name="code" maxlength="30" class="form-control" value="{{ old('code',$group->code) }}" required></div></div>
            <div class="form-group"><label class="col-sm-3 control-label required">Nama Kelompok</label><div class="col-sm-8"><input name="name" maxlength="100" class="form-control" value="{{ old('name',$group->name) }}" required></div></div>
            <div class="form-group"><label class="col-sm-3 control-label required">Kategori</label><div class="col-sm-8"><input name="category" list="group-categories" class="form-control" value="{{ old('category',$group->category) }}" required><datalist id="group-categories">@foreach($categories as $option)<option>{{ $option }}</option>@endforeach<option>Karang Taruna</option><option>PKK</option><option>Kelompok Tani</option><option>Kelompok Keagamaan</option><option>Kelompok Usaha</option></datalist></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">No. SK Pendirian</label><div class="col-sm-8"><input name="establishment_decree" maxlength="100" class="form-control" value="{{ old('establishment_decree',$group->establishment_decree) }}"></div></div>
            <div class="form-group"><label class="col-sm-3 control-label required">Ketua Kelompok</label><div class="col-sm-8"><select name="chairperson_id" class="form-control select2" required><option value="">Pilih penduduk</option>@foreach($residents as $resident)<option value="{{ $resident->id }}" @selected(old('chairperson_id',$group->chairperson_id)==$resident->id)>{{ $resident->nik }} — {{ $resident->name }}</option>@endforeach</select><p class="help-block">Ketua otomatis ditambahkan sebagai anggota kelompok.</p></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">Deskripsi</label><div class="col-sm-8"><textarea name="description" class="form-control" rows="5">{{ old('description',$group->description) }}</textarea></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">Status</label><div class="col-sm-8"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$group->exists?$group->is_active:true))> Kelompok aktif</label></div></div>
        </div>
        <div class="box-footer"><a href="{{ $group->exists?route('admin.population.groups.show',$group):route('admin.population.groups.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Kelompok</button></div>
    </div>
</form>
@endsection
