@extends('layouts.admin')
@php
    $titles=['profile'=>'Identitas Desa','vision-mission'=>'Visi Misi','history'=>'Sejarah Desa','potential'=>'Potensi Desa'];
    $section=$sections->first();
@endphp
@section('title',$titles[$page])
@section('page-description','Kelola informasi yang ditampilkan pada website desa')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ route('admin.village-content.update',$page) }}" data-dirty-form>
@csrf @method('put')
@if($page==='profile')
<div class="callout callout-info">
    <h4><i class="fa fa-info-circle"></i> Identitas Desa</h4>
    <p>Isi data umum desa di bawah ini. Nama dan NIP Kepala Desa mengikuti data Kepala Desa aktif pada menu Struktur Pemerintahan.</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-home"></i> Desa</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-8"><x-admin.village-input name="site_name" label="Nama Desa" :value="$settings['site.name'] ?? ''" maxlength="255" required /></div>
                    <div class="col-md-4"><x-admin.village-input name="postal_code" label="Kode Pos Desa" :value="$settings['village.postal_code'] ?? ''" maxlength="5" inputmode="numeric" placeholder="Contoh: 61152" /></div>
                    <div class="col-md-6"><x-admin.village-input name="village_code" label="Kode Desa" :value="$settings['village.code'] ?? ''" maxlength="20" inputmode="numeric" placeholder="Contoh: 35.25.04.2008" /></div>
                    <div class="col-md-6"><x-admin.village-input name="village_bps_code" label="Kode BPS Desa" :value="$settings['village.bps_code'] ?? ''" maxlength="20" inputmode="numeric" /></div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="village_head_name" class="control-label">Nama Kepala Desa</label>
                            <input id="village_head_name" class="form-control" value="{{ $villageHead?->full_name ?? '' }}" placeholder="Belum ada Kepala Desa aktif" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="village_head_nip" class="control-label">NIP Kepala Desa</label>
                            <input id="village_head_nip" class="form-control" value="{{ $villageHead?->nip ?? '' }}" placeholder="Belum diisi pada data perangkat desa" readonly>
                        </div>
                    </div>
                </div>
                <p class="help-block"><i class="fa fa-link"></i> Kelola data tersebut melalui <a href="{{ route('admin.officials.index') }}">Struktur Pemerintahan</a>.</p>

                <x-admin.village-input name="address" label="Alamat Kantor Desa" type="textarea" :value="$settings['site.address'] ?? ''" maxlength="1000" rows="3" />
                <div class="row">
                    <div class="col-md-6"><x-admin.village-input name="email" label="E-Mail Desa" type="email" :value="$settings['site.email'] ?? ''" maxlength="255" /></div>
                    <div class="col-md-6"><x-admin.village-input name="phone" label="Nomor Telepon Desa" type="tel" :value="$settings['site.phone'] ?? ''" maxlength="30" /></div>
                    <div class="col-md-6"><x-admin.village-input name="mobile" label="Nomor Ponsel Desa" type="tel" :value="$settings['village.mobile'] ?? ''" maxlength="30" /></div>
                    <div class="col-md-6"><x-admin.village-input name="website" label="Website Desa" type="url" :value="$settings['site.url'] ?? ''" maxlength="255" placeholder="https://desa.example.id" /></div>
                </div>
                <x-admin.village-input name="tagline" label="Tagline Website" :value="$settings['site.tagline'] ?? ''" maxlength="255" />
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Deskripsi Profil Desa</h3></div>
            <div class="box-body">
                <x-admin.village-input name="profile_content" label="Deskripsi Profil Desa" type="editor" :value="$sections->get('profile')?->content ?? ''" required />
                <x-admin.image-picker name="profile_image_id" label="Gambar Profil Desa" :media="$media" :selected="$sections->get('profile')?->image" />
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-map-marker"></i> Kecamatan</h3></div>
            <div class="box-body">
                <x-admin.village-input name="district_name" label="Nama Kecamatan" :value="$settings['district.name'] ?? ''" maxlength="100" />
                <x-admin.village-input name="district_code" label="Kode Kecamatan" :value="$settings['district.code'] ?? ''" maxlength="15" inputmode="numeric" />
                <x-admin.village-input name="district_head_name" label="Nama Camat" :value="$settings['district.head_name'] ?? ''" maxlength="255" />
                <x-admin.village-input name="district_head_nip" label="NIP Camat" :value="$settings['district.head_nip'] ?? ''" maxlength="30" inputmode="numeric" />
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-map"></i> Kabupaten</h3></div>
            <div class="box-body">
                <x-admin.village-input name="regency_name" label="Nama Kabupaten" :value="$settings['regency.name'] ?? ''" maxlength="100" />
                <x-admin.village-input name="regency_code" label="Kode Kabupaten" :value="$settings['regency.code'] ?? ''" maxlength="15" inputmode="numeric" />
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-globe"></i> Provinsi</h3></div>
            <div class="box-body">
                <x-admin.village-input name="province_name" label="Nama Provinsi" :value="$settings['province.name'] ?? ''" maxlength="100" />
                <x-admin.village-input name="province_code" label="Kode Provinsi" :value="$settings['province.code'] ?? ''" maxlength="10" inputmode="numeric" />
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-send"></i> Publikasi</h3></div>
            <div class="box-body">
                <div class="form-group {{ $errors->has('status')?'has-error':'' }}">
                    <label class="control-label" for="status">Status Publikasi</label>
                    <select id="status" name="status" class="form-control select2">
                        <option value="published" @selected(old('status',$section?->status ?? 'published')==='published')>Terbit</option>
                        <option value="draft" @selected(old('status',$section?->status)==='draft')>Draf</option>
                    </select>
                    @error('status')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="box-footer">
                <button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>
                <button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
@else
<div class="box box-info">
<div class="box-header with-border"><h3 class="box-title">Form {{ $titles[$page] }}</h3></div>
<div class="box-body">
@if($page==='vision-mission')
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
@endif
</form>
@endsection
