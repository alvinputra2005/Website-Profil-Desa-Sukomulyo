@extends('layouts.admin')
@section('title',$resident->exists?'Ubah Penduduk':'Tambah Penduduk')
@section('page-description','Form biodata dan pengelompokan penduduk')
@section('content')
@php
    $religions=['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu','Kepercayaan'];
    $marital=['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'];
    $education=['Tidak/Belum Sekolah','Belum Tamat SD/Sederajat','Tamat SD/Sederajat','SLTP/Sederajat','SLTA/Sederajat','Diploma I/II','Akademi/Diploma III','Diploma IV/Strata I','Strata II','Strata III'];
@endphp
<form method="post" action="{{ $resident->exists?route('admin.population.residents.update',$resident):route('admin.population.residents.store') }}" data-dirty-form>
    @csrf @if($resident->exists)@method('put')@endif
    <div class="nav-tabs-custom population-form-tabs">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#identity" data-toggle="tab">Biodata</a></li>
            <li><a href="#grouping" data-toggle="tab">Keluarga & Wilayah</a></li>
            <li><a href="#contact" data-toggle="tab">Kontak & Orang Tua</a></li>
            <li><a href="#event" data-toggle="tab">Peristiwa Penduduk</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="identity">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label class="required">NIK</label><input class="form-control" name="nik" maxlength="16" inputmode="numeric" value="{{ old('nik',$resident->nik) }}" required><p class="help-block">16 digit Nomor Induk Kependudukan.</p></div>
                        <div class="form-group"><label class="required">Nama Lengkap</label><input class="form-control" name="name" maxlength="100" value="{{ old('name',$resident->name) }}" required></div>
                        <div class="form-group"><label class="required">Jenis Kelamin</label><select name="sex" class="form-control" required><option value="">Pilih</option><option value="L" @selected(old('sex',$resident->sex)==='L')>Laki-laki</option><option value="P" @selected(old('sex',$resident->sex)==='P')>Perempuan</option></select></div>
                        <div class="row"><div class="col-sm-7"><div class="form-group"><label>Tempat Lahir</label><input class="form-control" name="birth_place" value="{{ old('birth_place',$resident->birth_place) }}"></div></div><div class="col-sm-5"><div class="form-group"><label>Tanggal Lahir</label><input type="date" class="form-control" name="birth_date" value="{{ old('birth_date',$resident->birth_date?->format('Y-m-d')) }}"></div></div></div>
                        <div class="form-group"><label>Agama</label><select name="religion" class="form-control select2"><option value="">Belum diisi</option>@foreach($religions as $option)<option @selected(old('religion',$resident->religion)===$option)>{{ $option }}</option>@endforeach</select></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>Status Perkawinan</label><select name="marital_status" class="form-control"><option value="">Belum diisi</option>@foreach($marital as $option)<option @selected(old('marital_status',$resident->marital_status)===$option)>{{ $option }}</option>@endforeach</select></div>
                        <div class="form-group"><label class="required">Kewarganegaraan</label><select name="citizenship" class="form-control"><option value="WNI" @selected(old('citizenship',$resident->citizenship??'WNI')==='WNI')>WNI</option><option value="WNA" @selected(old('citizenship',$resident->citizenship)==='WNA')>WNA</option></select></div>
                        <div class="form-group"><label>Pendidikan dalam KK</label><select name="education" class="form-control select2"><option value="">Belum diisi</option>@foreach($education as $option)<option @selected(old('education',$resident->education)===$option)>{{ $option }}</option>@endforeach</select></div>
                        <div class="form-group"><label>Pekerjaan</label><input class="form-control" name="occupation" list="occupation-list" value="{{ old('occupation',$resident->occupation) }}"><datalist id="occupation-list"><option>Belum/Tidak Bekerja</option><option>Pelajar/Mahasiswa</option><option>Petani/Pekebun</option><option>Wiraswasta</option><option>Karyawan Swasta</option><option>PNS</option><option>Mengurus Rumah Tangga</option></datalist></div>
                        <div class="form-group"><label>Golongan Darah</label><select name="blood_type" class="form-control"><option value="">Belum diisi</option>@foreach(['A','B','AB','O','-'] as $option)<option value="{{ $option }}" @selected(old('blood_type',$resident->blood_type)===$option)>{{ $option==='-'?'Tidak diketahui':$option }}</option>@endforeach</select></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="grouping">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>Keluarga / No. KK</label><select name="family_id" class="form-control select2"><option value="">Belum masuk keluarga</option>@foreach($families as $family)<option value="{{ $family->id }}" @selected(old('family_id',$resident->family_id)==$family->id)>{{ $family->family_card_number }} — {{ $family->head?->name ?: 'Kepala belum diisi' }}</option>@endforeach</select></div>
                        <div class="form-group"><label>Hubungan dalam Keluarga</label><input name="family_relationship" class="form-control" list="family-relations" value="{{ old('family_relationship',$resident->family_relationship) }}"><datalist id="family-relations"><option>Kepala Keluarga</option><option>Suami</option><option>Istri</option><option>Anak</option><option>Menantu</option><option>Cucu</option><option>Orang Tua</option><option>Famili Lain</option></datalist></div>
                        <div class="form-group"><label>Rumah Tangga</label><select name="household_id" class="form-control select2"><option value="">Belum masuk rumah tangga</option>@foreach($households as $household)<option value="{{ $household->id }}" @selected(old('household_id',$resident->household_id)==$household->id)>{{ $household->household_number }} — {{ $household->head?->name ?: 'Kepala belum diisi' }}</option>@endforeach</select></div>
                        <div class="form-group"><label>Hubungan dalam Rumah Tangga</label><input name="household_relationship" class="form-control" list="household-relations" value="{{ old('household_relationship',$resident->household_relationship) }}"><datalist id="household-relations"><option>Kepala Rumah Tangga</option><option>Anggota Rumah Tangga</option></datalist></div>
                    </div>
                    <div class="col-md-6">
                        <div class="row"><div class="col-sm-6"><div class="form-group"><label>Dusun</label><input name="hamlet" class="form-control" value="{{ old('hamlet',$resident->area?->hamlet) }}"></div></div><div class="col-sm-3"><div class="form-group"><label>RW</label><input name="rw" class="form-control" maxlength="3" value="{{ old('rw',$resident->area?->rw) }}"></div></div><div class="col-sm-3"><div class="form-group"><label>RT</label><input name="rt" class="form-control" maxlength="3" value="{{ old('rt',$resident->area?->rt) }}"></div></div></div>
                        <div class="form-group"><label>Alamat Sekarang</label><textarea name="current_address" class="form-control" rows="3">{{ old('current_address',$resident->current_address) }}</textarea></div>
                        <div class="form-group"><label>Alamat Sebelumnya</label><textarea name="previous_address" class="form-control" rows="3">{{ old('previous_address',$resident->previous_address) }}</textarea></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="contact">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>NIK Ayah</label><input name="father_nik" maxlength="16" class="form-control" value="{{ old('father_nik',$resident->father_nik) }}"></div>
                        <div class="form-group"><label>Nama Ayah</label><input name="father_name" class="form-control" value="{{ old('father_name',$resident->father_name) }}"></div>
                        <div class="form-group"><label>NIK Ibu</label><input name="mother_nik" maxlength="16" class="form-control" value="{{ old('mother_nik',$resident->mother_nik) }}"></div>
                        <div class="form-group"><label>Nama Ibu</label><input name="mother_name" class="form-control" value="{{ old('mother_name',$resident->mother_name) }}"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>Telepon</label><input name="phone" class="form-control" value="{{ old('phone',$resident->phone) }}"></div>
                        <div class="form-group"><label>Email</label><input name="email" type="email" class="form-control" value="{{ old('email',$resident->email) }}"></div>
                        <div class="form-group"><label>Catatan</label><textarea name="notes" class="form-control" rows="5">{{ old('notes',$resident->notes) }}</textarea></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="event">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label class="required">Status Penduduk</label><select name="resident_status" class="form-control"><option value="permanent" @selected(old('resident_status',$resident->resident_status??'permanent')==='permanent')>Tetap</option><option value="non_permanent" @selected(old('resident_status',$resident->resident_status)==='non_permanent')>Tidak Tetap</option></select></div>
                        <div class="form-group"><label class="required">Status Dasar</label><select name="status" class="form-control"><option value="active" @selected(old('status',$resident->status??'active')==='active')>Hidup / Aktif</option><option value="moved" @selected(old('status',$resident->status)==='moved')>Pindah</option><option value="deceased" @selected(old('status',$resident->status)==='deceased')>Meninggal</option><option value="missing" @selected(old('status',$resident->status)==='missing')>Hilang</option></select><p class="help-block">Perubahan status otomatis dicatat dalam laporan penduduk.</p></div>
                        <div class="form-group"><label>Tanggal Terdaftar</label><input type="date" name="registered_at" class="form-control" value="{{ old('registered_at',$resident->registered_at?->format('Y-m-d')) }}"></div>
                        @unless($resident->exists)<div class="form-group"><label class="required">Peristiwa Awal</label><select name="initial_event_type" class="form-control"><option value="arrival" @selected(old('initial_event_type','arrival')==='arrival')>Datang / Terdaftar</option><option value="birth" @selected(old('initial_event_type')==='birth')>Lahir</option></select></div>@endunless
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label class="{{ $resident->exists?'':'required' }}">Tanggal Peristiwa</label><input type="date" name="event_date" class="form-control" value="{{ old('event_date',$resident->exists?'':now()->format('Y-m-d')) }}"><p class="help-block">{{ $resident->exists?'Wajib diisi saat status dasar berubah.':'Tanggal lahir/datang untuk laporan bulanan.' }}</p></div>
                        <div class="form-group"><label>Tanggal Lapor</label><input type="date" name="reported_at" class="form-control" value="{{ old('reported_at',now()->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label>Alamat Tujuan (jika pindah)</label><input name="destination_address" class="form-control" value="{{ old('destination_address') }}"></div>
                        <div class="form-group"><label>Sebab / Keterangan Peristiwa</label><input name="event_cause" class="form-control" value="{{ old('event_cause') }}"><textarea name="event_notes" class="form-control population-event-notes" rows="2" placeholder="Catatan tambahan">{{ old('event_notes') }}</textarea></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <a href="{{ $resident->exists?route('admin.population.residents.show',$resident):route('admin.population.residents.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Penduduk</button>
        </div>
    </div>
</form>
@endsection
