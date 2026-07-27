@extends('layouts.admin')
@php
    $titles=['profile'=>'Identitas Desa','vision-mission'=>'Visi Misi','history'=>'Sejarah Desa','potential'=>'Potensi Desa'];
    $section=$sections->first();
    $regionValues=[
        'province'=>['code'=>(string) old('province_code',$settings['province.code'] ?? ''),'name'=>(string) old('province_name',$settings['province.name'] ?? '')],
        'regency'=>['code'=>(string) old('regency_code',$settings['regency.code'] ?? ''),'name'=>(string) old('regency_name',$settings['regency.name'] ?? '')],
        'district'=>['code'=>(string) old('district_code',$settings['district.code'] ?? ''),'name'=>(string) old('district_name',$settings['district.name'] ?? '')],
        'village'=>['code'=>(string) old('village_code',$settings['village.code'] ?? ''),'name'=>(string) old('site_name',$settings['site.name'] ?? '')],
    ];
@endphp
@section('title',$titles[$page])
@section('page-description','Kelola informasi yang ditampilkan pada website desa')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ match ($page) {
    'profile' => route('admin.village-content.profile-update'),
    'vision-mission' => route('admin.village-content.vision-mission.update'),
    default => route('admin.village-content.update', $page),
} }}" class="village-content-form village-content-{{ $page }}-form" data-dirty-form>
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
                    <div class="col-md-6"><x-admin.village-input name="village_bps_code" label="Kode BPS Desa" :value="$settings['village.bps_code'] ?? ''" maxlength="20" inputmode="numeric" /></div>
                    <div class="col-md-6"><x-admin.village-input name="postal_code" label="Kode Pos Desa" :value="$settings['village.postal_code'] ?? ''" maxlength="5" inputmode="numeric" placeholder="Contoh: 61152" /></div>
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
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="dropdown profile-save-dropdown">
            <button type="button" class="btn btn-social btn-info btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-save"></i> Simpan Perubahan <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li>
                    <button type="submit" data-submit-status="published">
                        <i class="fa fa-check"></i> Simpan Perubahan
                    </button>
                </li>
                <li>
                    <button type="submit" data-submit-status="draft">
                        <i class="fa fa-file-text-o"></i> Simpan sebagai Draf
                    </button>
                </li>
            </ul>
            <input type="hidden" id="status" name="status" value="{{ old('status',$section?->status ?? 'published') }}" data-publication-status>
            @error('status')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="box box-info" data-region-selector data-regions-base-url="{{ url('/admin/wilayah') }}">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-map"></i> Wilayah Administratif</h3></div>
            <div class="box-body">
                <p class="help-block">Pilih wilayah secara berurutan mulai dari provinsi hingga desa/kelurahan.</p>

                @foreach([
                    'province'=>['Provinsi','province_code','province_name'],
                    'regency'=>['Kabupaten/Kota','regency_code','regency_name'],
                    'district'=>['Kecamatan','district_code','district_name'],
                    'village'=>['Desa/Kelurahan','village_code','site_name'],
                ] as $regionKey=>[$regionLabel,$codeField,$nameField])
                    <div class="form-group {{ $errors->has($codeField)||$errors->has($nameField)?'has-error':'' }}">
                        <label for="{{ $codeField }}" class="control-label required">{{ $regionLabel }}</label>
                        <select
                            id="{{ $codeField }}"
                            name="{{ $codeField }}"
                            class="form-control select2"
                            data-region="{{ $regionKey }}"
                            data-selected-code="{{ $regionValues[$regionKey]['code'] }}"
                            data-selected-name="{{ $regionValues[$regionKey]['name'] }}"
                            required
                            @disabled($regionKey!=='province' && !$regionValues[$regionKey]['code'])
                        >
                            <option value="">-- Pilih {{ strtolower($regionLabel) }} --</option>
                            @if($regionValues[$regionKey]['code'])
                                <option value="{{ $regionValues[$regionKey]['code'] }}" data-name="{{ $regionValues[$regionKey]['name'] }}" selected>
                                    {{ $regionValues[$regionKey]['name'] }} ({{ $regionValues[$regionKey]['code'] }})
                                </option>
                            @endif
                        </select>
                        <input
                            type="hidden"
                            id="{{ $nameField }}"
                            name="{{ $nameField }}"
                            value="{{ $regionValues[$regionKey]['name'] }}"
                            data-region-name="{{ $regionKey }}"
                        >
                        @error($codeField)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                        @error($nameField)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-image"></i> Gambar Profil Desa</h3></div>
            <div class="box-body">
                <x-admin.image-picker name="profile_image_id" label="Gambar Profil Desa" :media="$media" :selected="$sections->get('profile')?->image" :show-label="false" />
            </div>
        </div>
    </div>
</div>
@else
@if($page==='history')
@php($selectedMediaId=old('image_id',$section?->image_id))
@php($selectedMedia=$media->firstWhere('id',(int)$selectedMediaId) ?? $section?->image)
<div class="row">
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                @if($section?->status === 'published')
                    <a class="btn btn-success btn-sm" target="_blank" href="{{ route('profile-desa.detail','sejarah') }}">
                        <i class="fa fa-eye"></i> Lihat Halaman
                    </a>
                @endif
            </div>
            <div class="box-body">
                <x-admin.village-input
                    name="title"
                    label="Judul Halaman"
                    :value="$section?->title ?? 'Sejarah Desa'"
                    required
                />
                <x-admin.village-input
                    name="content"
                    label="Isi Sejarah Desa"
                    type="editor"
                    :value="$section?->content ?? ''"
                    required
                />
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <button type="submit" class="btn btn-social btn-info btn-block" style="margin-bottom: 15px">
            <i class="fa fa-save"></i> Simpan Sejarah Desa
        </button>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-image"></i> Gambar Utama</h3>
            </div>
            <div class="box-body">
                <x-admin.image-picker
                    name="image_id"
                    label="Gambar Sejarah Desa"
                    :media="$media"
                    :selected="$selectedMedia"
                    :show-label="false"
                />
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-send"></i> Publikasi</h3>
            </div>
            <div class="box-body">
                <div class="form-group {{ $errors->has('status')?'has-error':'' }}">
                    <label class="control-label required" for="status">Status Halaman</label>
                    <select id="status" name="status" class="form-control select2" required>
                        <option value="draft" @selected(old('status',$section?->status)==='draft')>Draf</option>
                        <option value="published" @selected(old('status',$section?->status ?? 'published')==='published')>Terbit</option>
                    </select>
                    @error('status')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <p class="help-block">Halaman hanya tampil kepada pengunjung saat berstatus Terbit.</p>
            </div>
        </div>
    </div>
</div>
@elseif($page==='vision-mission')
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Visi Desa</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-label="Buka atau tutup form visi desa">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <x-admin.village-input name="vision" label="Isi Visi Desa" type="editor" :value="$sections->get('vision')?->content ?? ''" required />
    </div>
</div>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Misi Desa</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-label="Buka atau tutup form misi desa">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <x-admin.village-input name="mission" label="Isi Misi Desa" type="editor" :value="$sections->get('mission')?->content ?? ''" required />
    </div>
</div>

<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-send"></i> Publikasi</h3></div>
    <div class="box-body">
        <div class="form-group {{ $errors->has('status')?'has-error':'' }}"><label class="control-label">Status Publikasi</label><select name="status" class="form-control select2"><option value="published" @selected(old('status',$section?->status ?? 'published')==='published')>Terbit</option><option value="draft" @selected(old('status',$section?->status)==='draft')>Draf</option></select>@error('status')<span class="field-error">{{ $message }}</span>@enderror</div>
    </div>
    <div class="box-footer vision-mission-actions"><a href="{{ route('admin.dashboard') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Perubahan</button></div>
</div>
@else
<div class="box box-info">
<div class="box-header with-border"><h3 class="box-title">Form {{ $titles[$page] }}</h3></div>
<div class="box-body">
    <x-admin.village-input name="title" label="Judul Halaman" :value="$section?->title ?? $titles[$page]" required />
    <x-admin.village-input name="content" :label="$page==='history'?'Isi Sejarah Desa':'Deskripsi Potensi Desa'" type="editor" :value="$section?->content ?? ''" required />
    <x-admin.image-picker name="image_id" :label="$page==='history'?'Gambar Sejarah Desa':'Gambar Potensi Desa'" :media="$media" :selected="$section?->image" />
<div class="form-group {{ $errors->has('status')?'has-error':'' }}"><label class="control-label">Status Publikasi</label><select name="status" class="form-control select2"><option value="published" @selected(old('status',$section?->status ?? 'published')==='published')>Terbit</option><option value="draft" @selected(old('status',$section?->status)==='draft')>Draf</option></select>@error('status')<span class="field-error">{{ $message }}</span>@enderror</div>
</div>
<div class="box-footer"><button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Perubahan</button></div>
</div>
@endif
@endif
</form>
@endsection
