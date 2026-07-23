@extends('layouts.admin')
@section('title', ($official->exists ? 'Ubah' : 'Tambah').' Perangkat Desa')
@section('page-title', 'Staf Pemerintah Desa')
@section('page-description', $official->exists ? 'Ubah Data' : 'Tambah Data')

@php
    $source = old('source', $official->resident_id ? 'resident' : 'external');
    $socialValues = [
        'facebook' => old('facebook', $social['facebook'] ?? ''),
        'instagram' => old('instagram', $social['instagram'] ?? ''),
        'youtube' => old('youtube', $social['youtube'] ?? ''),
        'x' => old('x', $social['x'] ?? ''),
    ];
@endphp

@section('content')
<form id="official-form" method="post" enctype="multipart/form-data" action="{{ $official->exists ? route('admin.officials.update', $official) : route('admin.officials.store') }}" data-dirty-form>
    @csrf
    @if($official->exists) @method('put') @endif

    <div class="box box-info official-source-box">
        <div class="box-header with-border">
            <a href="{{ route('admin.officials.index') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-arrow-circle-left"></i> Kembali ke Daftar Staf Pemerintah Desa</a>
        </div>
        <div class="box-body form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label required">Data Staf</label>
                <div class="col-sm-8">
                    <div class="btn-group official-source-toggle" data-toggle="buttons">
                        <label class="btn btn-info btn-sm {{ $source === 'resident' ? 'active' : '' }}">
                            <input type="radio" name="source" value="resident" autocomplete="off" @checked($source === 'resident')> Dari Database Penduduk
                        </label>
                        <label class="btn btn-info btn-sm {{ $source === 'external' ? 'active' : '' }}">
                            <input type="radio" name="source" value="external" autocomplete="off" @checked($source === 'external')> Tidak Terdata
                        </label>
                    </div>
                    @error('source')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-group" data-resident-picker>
                <label for="resident_id" class="col-sm-2 control-label required">NIK / Nama Penduduk</label>
                <div class="col-sm-8">
                    <select id="resident_id" name="resident_id" class="form-control select2" data-placeholder="-- Silakan Masukkan NIK / Nama --">
                        <option value="">-- Silakan Masukkan NIK / Nama --</option>
                        @foreach($residents as $resident)
                            <option
                                value="{{ $resident->id }}"
                                data-name="{{ $resident->name }}"
                                data-nik="{{ $resident->nik }}"
                                data-birth-place="{{ $resident->birth_place }}"
                                data-birth-date="{{ $resident->birth_date?->format('Y-m-d') }}"
                                data-sex="{{ $resident->sex }}"
                                data-education="{{ $resident->education }}"
                                data-religion="{{ $resident->religion }}"
                                @selected((string) old('resident_id', $official->resident_id) === (string) $resident->id)
                            >{{ $resident->nik }} — {{ $resident->name }}{{ $resident->area?->hamlet ? ' — '.$resident->area->hamlet : '' }}</option>
                        @endforeach
                    </select>
                    @error('resident_id')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                    <p class="help-block">Data identitas akan disalin dari database penduduk saat disimpan.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row official-form-layout">
        <div class="col-md-3">
            <div class="box box-primary official-photo-box">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-camera"></i> Foto Staf</h3></div>
                <div class="box-body">
                    <x-admin.image-picker name="photo_id" label="Foto perangkat desa" :media="$media" :selected="$official->photo" :show-label="false" />
                    <div class="official-camera-control">
                        <label for="photo_camera" class="btn btn-danger btn-block btn-sm"><i class="fa fa-camera"></i> Ambil dari Kamera</label>
                        <input id="photo_camera" name="photo_camera" type="file" accept="image/jpeg,image/png,image/webp" capture="user" class="sr-only">
                        <p class="help-block">Kamera tersedia pada perangkat yang mendukung. Foto otomatis dioptimalkan.</p>
                    </div>
                    @error('photo_camera')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Media Sosial</h3></div>
                <div class="box-body official-social-fields">
                    @foreach([
                        'facebook' => ['Facebook', 'fa-facebook', 'https://facebook.com/...'],
                        'instagram' => ['Instagram', 'fa-instagram', 'https://instagram.com/...'],
                        'youtube' => ['YouTube', 'fa-youtube-play', 'https://youtube.com/...'],
                        'x' => ['X / Twitter', 'fa-twitter', 'https://x.com/...'],
                    ] as $field => [$label, $icon, $placeholder])
                        <div class="form-group">
                            <label for="{{ $field }}"><i class="fa {{ $icon }}"></i> {{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="url" class="form-control input-sm" value="{{ $socialValues[$field] }}" placeholder="{{ $placeholder }}">
                            @error($field)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-user"></i> Identitas dan Kepegawaian</h3></div>
                <div class="box-body form-horizontal official-main-fields">
                    <div class="form-group">
                        <label for="name" class="col-sm-4 control-label required">Nama Pegawai Desa</label>
                        <div class="col-sm-7">
                            <input id="name" name="name" class="form-control input-sm" maxlength="255" value="{{ old('name', $official->name) }}" placeholder="Nama lengkap" data-resident-value="name" required>
                            @error('name')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Nama dengan Gelar</label>
                        <div class="col-sm-7"><input class="form-control input-sm" value="{{ $official->full_name }}" placeholder="Nama dengan gelar" data-full-name-preview disabled></div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Gelar</label>
                        <div class="col-sm-3"><input id="title_prefix" name="title_prefix" class="form-control input-sm" maxlength="50" value="{{ old('title_prefix', $official->title_prefix) }}" placeholder="Gelar depan"></div>
                        <div class="col-sm-4"><input id="title_suffix" name="title_suffix" class="form-control input-sm" maxlength="50" value="{{ old('title_suffix', $official->title_suffix) }}" placeholder="Gelar belakang"></div>
                    </div>
                    <div class="form-group">
                        <label for="nik" class="col-sm-4 control-label">Nomor Induk Kependudukan</label>
                        <div class="col-sm-7">
                            <input id="nik" name="nik" class="form-control input-sm" maxlength="16" inputmode="numeric" value="{{ old('nik', $official->nik) }}" placeholder="16 digit NIK" data-resident-value="nik">
                            @error('nik')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="village_employee_number" class="col-sm-4 control-label">NIPD</label>
                        <div class="col-sm-7"><input id="village_employee_number" name="village_employee_number" class="form-control input-sm" maxlength="25" value="{{ old('village_employee_number', $official->village_employee_number) }}" placeholder="Nomor Induk Perangkat Desa">@error('village_employee_number')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="nip" class="col-sm-4 control-label">NIP</label>
                        <div class="col-sm-7"><input id="nip" name="nip" class="form-control input-sm" maxlength="30" value="{{ old('nip', $official->nip) }}" placeholder="Nomor Induk Pegawai">@error('nip')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="id_card_tag" class="col-sm-4 control-label">Tag ID Card</label>
                        <div class="col-sm-7"><input id="id_card_tag" name="id_card_tag" class="form-control input-sm" maxlength="50" value="{{ old('id_card_tag', $official->id_card_tag) }}" placeholder="Tag ID Card">@error('id_card_tag')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="birth_place" class="col-sm-4 control-label">Tempat Lahir</label>
                        <div class="col-sm-7"><input id="birth_place" name="birth_place" class="form-control input-sm" maxlength="100" value="{{ old('birth_place', $official->birth_place) }}" placeholder="Tempat lahir" data-resident-value="birthPlace">@error('birth_place')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="birth_date" class="col-sm-4 control-label">Tanggal Lahir</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm"><span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="birth_date" name="birth_date" type="date" class="form-control" value="{{ old('birth_date', $official->birth_date?->format('Y-m-d')) }}" data-resident-value="birthDate"></div>
                            @error('birth_date')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sex" class="col-sm-4 control-label">Jenis Kelamin</label>
                        <div class="col-sm-7"><select id="sex" name="sex" class="form-control input-sm" data-resident-value="sex"><option value="">Pilih jenis kelamin</option><option value="L" @selected(old('sex', $official->sex) === 'L')>Laki-laki</option><option value="P" @selected(old('sex', $official->sex) === 'P')>Perempuan</option></select>@error('sex')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="education" class="col-sm-4 control-label">Pendidikan</label>
                        <div class="col-sm-7"><select id="education" name="education" class="form-control input-sm select2" data-resident-value="education"><option value="">Pilih pendidikan</option>@foreach($educationOptions as $option)<option value="{{ $option }}" @selected(old('education', $official->education) === $option)>{{ strtoupper($option) }}</option>@endforeach</select>@error('education')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="religion" class="col-sm-4 control-label">Agama</label>
                        <div class="col-sm-7"><select id="religion" name="religion" class="form-control input-sm select2" data-resident-value="religion"><option value="">Pilih agama</option>@foreach($religions as $option)<option value="{{ $option }}" @selected(old('religion', $official->religion) === $option)>{{ $option }}</option>@endforeach</select>@error('religion')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="rank_grade" class="col-sm-4 control-label">Pangkat / Golongan</label>
                        <div class="col-sm-7"><input id="rank_grade" name="rank_grade" class="form-control input-sm" maxlength="50" value="{{ old('rank_grade', $official->rank_grade) }}" placeholder="Pangkat / Golongan">@error('rank_grade')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>

                    <hr>
                    <h4 class="official-form-section"><i class="fa fa-briefcase"></i> Jabatan dan Keputusan</h4>
                    <div class="form-group">
                        <label for="appointment_decree" class="col-sm-4 control-label">Nomor Keputusan Pengangkatan</label>
                        <div class="col-sm-7"><input id="appointment_decree" name="appointment_decree" class="form-control input-sm" maxlength="100" value="{{ old('appointment_decree', $official->appointment_decree) }}" placeholder="Nomor SK pengangkatan">@error('appointment_decree')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="appointment_date" class="col-sm-4 control-label">Tanggal Keputusan Pengangkatan</label>
                        <div class="col-sm-7"><div class="input-group input-group-sm"><span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="appointment_date" name="appointment_date" type="date" class="form-control" value="{{ old('appointment_date', $official->appointment_date?->format('Y-m-d')) }}"></div>@error('appointment_date')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="dismissal_decree" class="col-sm-4 control-label">Nomor Keputusan Pemberhentian</label>
                        <div class="col-sm-7"><input id="dismissal_decree" name="dismissal_decree" class="form-control input-sm" maxlength="100" value="{{ old('dismissal_decree', $official->dismissal_decree) }}" placeholder="Nomor SK pemberhentian">@error('dismissal_decree')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="dismissal_date" class="col-sm-4 control-label">Tanggal Keputusan Pemberhentian</label>
                        <div class="col-sm-7"><div class="input-group input-group-sm"><span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="dismissal_date" name="dismissal_date" type="date" class="form-control" value="{{ old('dismissal_date', $official->dismissal_date?->format('Y-m-d')) }}"></div>@error('dismissal_date')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="term" class="col-sm-4 control-label">Masa Jabatan (Usia/Periode)</label>
                        <div class="col-sm-7"><input id="term" name="term" class="form-control input-sm" maxlength="150" value="{{ old('term', $official->term) }}" placeholder="Contoh: 6 Tahun, Periode 2025–2031">@error('term')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="position" class="col-sm-4 control-label required">Jabatan</label>
                        <div class="col-sm-7">
                            <input id="position" name="position" class="form-control input-sm" list="official-position-options" maxlength="255" value="{{ old('position', $official->position) }}" placeholder="Pilih atau ketik jabatan" required>
                            <datalist id="official-position-options">@foreach(collect($defaultPositions)->merge($positions)->unique() as $position)<option value="{{ $position }}"></option>@endforeach</datalist>
                            @error('position')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Status Pejabat</label>
                        <div class="col-sm-7">
                            <input type="hidden" name="is_acting" value="0">
                            <label class="checkbox-inline"><input type="checkbox" name="is_acting" value="1" @checked(old('is_acting', $official->is_acting))> Penjabat / Pelaksana tugas</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="superior_id" class="col-sm-4 control-label">Atasan</label>
                        <div class="col-sm-7"><select id="superior_id" name="superior_id" class="form-control input-sm select2"><option value="">Tidak ada / Jabatan tertinggi</option>@foreach($superiors as $superior)<option value="{{ $superior->id }}" @selected((string) old('superior_id', $official->superior_id) === (string) $superior->id)>{{ $superior->full_name }} ({{ $superior->position }})</option>@endforeach</select>@error('superior_id')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>

                    <hr>
                    <h4 class="official-form-section"><i class="fa fa-sitemap"></i> Pengaturan Bagan Organisasi</h4>
                    <div class="form-group">
                        <label for="organization_level" class="col-sm-4 control-label">Bagan — Tingkat</label>
                        <div class="col-sm-7"><input id="organization_level" name="organization_level" type="number" min="1" max="20" class="form-control input-sm" value="{{ old('organization_level', $official->organization_level) }}" placeholder="Contoh: 2">@error('organization_level')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="organization_offset" class="col-sm-4 control-label">Bagan — Offset (%)</label>
                        <div class="col-sm-7"><input id="organization_offset" name="organization_offset" type="number" min="-100" max="100" class="form-control input-sm" value="{{ old('organization_offset', $official->organization_offset ?? 0) }}" placeholder="-100 sampai 100">@error('organization_offset')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="organization_layout" class="col-sm-4 control-label">Bagan — Layout</label>
                        <div class="col-sm-7"><select id="organization_layout" name="organization_layout" class="form-control input-sm"><option value="">Otomatis</option><option value="hanging" @selected(old('organization_layout', $official->organization_layout) === 'hanging')>Hanging</option><option value="horizontal" @selected(old('organization_layout', $official->organization_layout) === 'horizontal')>Horizontal</option></select>@error('organization_layout')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="organization_color" class="col-sm-4 control-label">Bagan — Warna</label>
                        <div class="col-sm-7"><div class="input-group input-group-sm"><input id="organization_color" name="organization_color" type="text" class="form-control" value="{{ old('organization_color', $official->organization_color ?: '#526b42') }}" pattern="#[0-9A-Fa-f]{6}" placeholder="#526B42"><span class="input-group-addon official-color-preview" style="background:{{ old('organization_color', $official->organization_color ?: '#526b42') }}"></span></div>@error('organization_color')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>

                    <hr>
                    <h4 class="official-form-section"><i class="fa fa-cog"></i> Status dan Informasi Tambahan</h4>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Status Pegawai Desa</label>
                        <div class="col-sm-7">
                            <input type="hidden" name="is_active" value="0">
                            <label class="checkbox-inline"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $official->exists ? $official->is_active : true))> Aktif</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Hak Penandatanganan</label>
                        <div class="col-sm-7">
                            <input type="hidden" name="can_sign_on_behalf" value="0"><label class="checkbox-inline"><input type="checkbox" name="can_sign_on_behalf" value="1" @checked(old('can_sign_on_behalf', $official->can_sign_on_behalf))> a.n. (atas nama)</label>
                            <input type="hidden" name="can_sign_for" value="0"><label class="checkbox-inline"><input type="checkbox" name="can_sign_for" value="1" @checked(old('can_sign_for', $official->can_sign_for))> u.b. (untuk beliau)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="display_order" class="col-sm-4 control-label required">Urutan Tampil</label>
                        <div class="col-sm-7"><input id="display_order" name="display_order" type="number" min="0" class="form-control input-sm" value="{{ old('display_order', $official->display_order ?? 0) }}" required>@error('display_order')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="registered_at" class="col-sm-4 control-label">Tanggal Terdaftar</label>
                        <div class="col-sm-7"><input id="registered_at" name="registered_at" type="date" class="form-control input-sm" value="{{ old('registered_at', $official->registered_at?->format('Y-m-d')) }}">@error('registered_at')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="col-sm-4 control-label">Telepon</label>
                        <div class="col-sm-7"><input id="phone" name="phone" class="form-control input-sm" maxlength="25" value="{{ old('phone', $official->phone) }}" placeholder="Nomor telepon">@error('phone')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">Email</label>
                        <div class="col-sm-7"><input id="email" name="email" type="email" class="form-control input-sm" maxlength="150" value="{{ old('email', $official->email) }}" placeholder="nama@desa.id">@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-group">
                        <label for="biography" class="col-sm-4 control-label">Biografi / Catatan</label>
                        <div class="col-sm-7"><textarea id="biography" name="biography" class="form-control" rows="5" maxlength="5000" placeholder="Riwayat singkat, tugas, atau catatan lain">{{ old('biography', $official->biography) }}</textarea>@error('biography')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.officials.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
                    <button type="reset" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" class="btn btn-social btn-info btn-sm pull-right"><i class="fa fa-check"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
