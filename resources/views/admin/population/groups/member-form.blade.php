@extends('layouts.admin')
@section('title',$membership->exists?'Ubah Anggota':'Tambah Anggota')
@section('page-description',$group->name)
@section('content')
<form method="post" action="{{ $membership->exists?route('admin.population.groups.members.update',[$group,$membership]):route('admin.population.groups.members.store',$group) }}" data-dirty-form>
    @csrf @if($membership->exists)@method('put')@endif
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Form Anggota {{ $group->name }}</h3></div>
        <div class="box-body form-horizontal">
            <div class="form-group"><label class="col-sm-3 control-label required">Nama Anggota</label><div class="col-sm-8"><select name="resident_id" class="form-control select2" required><option value="">Pilih penduduk</option>@foreach($residents as $resident)<option value="{{ $resident->id }}" @selected(old('resident_id',$membership->resident_id)==$resident->id)>{{ $resident->nik }} — {{ $resident->name }}</option>@endforeach</select></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">Nomor Anggota</label><div class="col-sm-8"><input name="member_number" maxlength="30" class="form-control" value="{{ old('member_number',$membership->member_number) }}"></div></div>
            <div class="form-group"><label class="col-sm-3 control-label required">Jabatan</label><div class="col-sm-8"><input name="position" list="positions" class="form-control" value="{{ old('position',$membership->position?:'Anggota') }}" required><datalist id="positions"><option>Ketua</option><option>Wakil Ketua</option><option>Sekretaris</option><option>Bendahara</option><option>Anggota</option></datalist></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">No. SK Pengangkatan</label><div class="col-sm-5"><input name="appointment_decree" class="form-control" value="{{ old('appointment_decree',$membership->appointment_decree) }}"></div><div class="col-sm-3"><input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date',$membership->appointment_date?->format('Y-m-d')) }}"></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">No. SK Pemberhentian</label><div class="col-sm-5"><input name="dismissal_decree" class="form-control" value="{{ old('dismissal_decree',$membership->dismissal_decree) }}"></div><div class="col-sm-3"><input type="date" name="dismissal_date" class="form-control" value="{{ old('dismissal_date',$membership->dismissal_date?->format('Y-m-d')) }}"></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">Periode</label><div class="col-sm-8"><input name="period" class="form-control" placeholder="Contoh: 2026–2029" value="{{ old('period',$membership->period) }}"></div></div>
            <div class="form-group"><label class="col-sm-3 control-label">Keterangan</label><div class="col-sm-8"><textarea name="notes" class="form-control" rows="4">{{ old('notes',$membership->notes) }}</textarea></div></div>
        </div>
        <div class="box-footer"><a href="{{ route('admin.population.groups.show',$group) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Anggota</button></div>
    </div>
</form>
@endsection
