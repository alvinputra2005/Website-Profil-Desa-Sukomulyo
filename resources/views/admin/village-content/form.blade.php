@extends('layouts.admin')
@php
    $titles=['profile'=>'Profil Desa','vision-mission'=>'Visi Misi','history'=>'Sejarah Desa','potential'=>'Potensi Desa'];
    $section=$sections->first();
@endphp
@section('title',$titles[$page])
@section('page-description','Kelola informasi yang ditampilkan pada website desa')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ route('admin.village-content.update',$page) }}" data-dirty-form>
@csrf @method('put')
<div class="box box-info">
<div class="box-header with-border"><h3 class="box-title">Form {{ $titles[$page] }}</h3></div>
<div class="box-body">
@if($page==='profile')
    <div class="row">
        <div class="col-md-6"><x-admin.village-input name="site_name" label="Nama Desa" :value="$settings['site.name'] ?? ''" required /></div>
        <div class="col-md-6"><x-admin.village-input name="tagline" label="Tagline Website" :value="$settings['site.tagline'] ?? ''" /></div>
        <div class="col-md-6"><x-admin.village-input name="email" label="Email Desa" type="email" :value="$settings['site.email'] ?? ''" /></div>
        <div class="col-md-6"><x-admin.village-input name="phone" label="Nomor Telepon" :value="$settings['site.phone'] ?? ''" /></div>
    </div>
    <x-admin.village-input name="address" label="Alamat Kantor Desa" type="textarea" :value="$settings['site.address'] ?? ''" />
    <x-admin.village-input name="profile_content" label="Deskripsi Profil Desa" type="editor" :value="$sections->get('profile')?->content ?? ''" required />
    <x-admin.image-picker name="profile_image_id" label="Gambar Profil Desa" :media="$media" :selected="$sections->get('profile')?->image" />
@elseif($page==='vision-mission')
    <x-admin.village-input name="vision" label="Visi Desa" type="editor" :value="$sections->get('vision')?->content ?? ''" required />
    <x-admin.image-picker name="vision_image_id" label="Gambar Visi Desa" :media="$media" :selected="$sections->get('vision')?->image" />
    <x-admin.village-input name="mission" label="Misi Desa" type="editor" :value="$sections->get('mission')?->content ?? ''" required />
    <x-admin.image-picker name="mission_image_id" label="Gambar Misi Desa" :media="$media" :selected="$sections->get('mission')?->image" />
@else
    <x-admin.village-input name="title" label="Judul Halaman" :value="$section?->title ?? $titles[$page]" required />
    <x-admin.village-input name="content" :label="$page==='history'?'Isi Sejarah Desa':'Deskripsi Potensi Desa'" type="editor" :value="$section?->content ?? ''" required />
    <x-admin.image-picker name="image_id" :label="$page==='history'?'Gambar Sejarah Desa':'Gambar Potensi Desa'" :media="$media" :selected="$section?->image" />
@endif
<div class="form-group {{ $errors->has('status')?'has-error':'' }}"><label class="control-label">Status Publikasi</label><select name="status" class="form-control select2"><option value="published" @selected(old('status',$section?->status ?? 'published')==='published')>Terbit</option><option value="draft" @selected(old('status',$section?->status)==='draft')>Draf</option></select>@error('status')<span class="field-error">{{ $message }}</span>@enderror</div>
</div>
<div class="box-footer"><button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Perubahan</button></div>
</div>
</form>
@endsection
