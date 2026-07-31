@extends('layouts.admin')
@section('title', 'Tambah Kategori Dataset')
@section('page-description', 'Buat kelompok baru untuk dataset statistik desa')
@section('content')
<form method="post" action="{{ route('admin.statistics.categories.store-category') }}">
    @csrf
    <div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-folder-open"></i> Informasi Kategori</h3></div>
        <div class="box-body">
            <div class="form-group @error('name') has-error @enderror">
                <label for="name">Nama Kategori</label>
                <input id="name" name="name" class="form-control" value="{{ old('name') }}" maxlength="100" required placeholder="Contoh: Kesehatan Desa">
                @error('name')<span class="help-block">{{ $message }}</span>@enderror
            </div>
            <div class="form-group @error('description') has-error @enderror">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" class="form-control" rows="3" maxlength="5000">{{ old('description') }}</textarea>
                @error('description')<span class="help-block">{{ $message }}</span>@enderror
            </div>
            <div class="form-group @error('icon') has-error @enderror">
                <label for="icon">Ikon Font Awesome <small class="text-muted">(opsional, contoh: fa-heartbeat)</small></label>
                <input id="icon" name="icon" class="form-control" value="{{ old('icon', 'fa-table') }}" maxlength="80">
                @error('icon')<span class="help-block">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="box-footer">
            <a href="{{ route('admin.statistics.index') }}" class="btn btn-default">Batal</a>
            <button class="btn btn-success pull-right"><i class="fa fa-save"></i> Simpan Kategori</button>
        </div>
    </div>
</form>
@endsection
