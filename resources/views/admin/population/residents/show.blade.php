@extends('layouts.admin')
@section('title','Rincian Penduduk')
@section('page-description',$resident->name)
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-body box-profile">
                <div class="population-avatar">{{ strtoupper(substr($resident->name,0,1)) }}</div>
                <h3 class="profile-username text-center">{{ $resident->name }}</h3>
                <p class="text-muted text-center">{{ $resident->nik }}</p>
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item"><b>Jenis Kelamin</b><span class="pull-right">{{ $resident->sex_label }}</span></li>
                    <li class="list-group-item"><b>Tempat/Tgl Lahir</b><span class="pull-right">{{ $resident->birth_place ?: '—' }}, {{ $resident->birth_date?->format('d-m-Y') ?: '—' }}</span></li>
                    <li class="list-group-item"><b>Status</b><span class="pull-right">{{ ['active'=>'Aktif','moved'=>'Pindah','deceased'=>'Meninggal','missing'=>'Hilang'][$resident->status] ?? $resident->status }}</span></li>
                </ul>
                <a href="{{ route('admin.population.residents.edit',$resident) }}" class="btn btn-primary btn-block"><i class="fa fa-edit"></i> Ubah Biodata</a>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs"><li class="active"><a href="#data" data-toggle="tab">Data Kependudukan</a></li><li><a href="#history" data-toggle="tab">Riwayat Peristiwa</a></li><li><a href="#groups" data-toggle="tab">Keanggotaan Kelompok</a></li></ul>
            <div class="tab-content">
                <div class="active tab-pane" id="data">
                    <dl class="dl-horizontal population-details">
                        <dt>No. KK</dt><dd>{{ $resident->family?->family_card_number ?? 'Belum masuk keluarga' }}</dd>
                        <dt>Hubungan Keluarga</dt><dd>{{ $resident->family_relationship ?: '—' }}</dd>
                        <dt>Rumah Tangga</dt><dd>{{ $resident->household?->household_number ?? 'Belum masuk rumah tangga' }}</dd>
                        <dt>Hubungan RT</dt><dd>{{ $resident->household_relationship ?: '—' }}</dd>
                        <dt>Wilayah</dt><dd>{{ $resident->area?->label ?? 'Belum diisi' }}</dd>
                        <dt>Alamat</dt><dd>{{ $resident->current_address ?: '—' }}</dd>
                        <dt>Agama</dt><dd>{{ $resident->religion ?: '—' }}</dd>
                        <dt>Pendidikan</dt><dd>{{ $resident->education ?: '—' }}</dd>
                        <dt>Pekerjaan</dt><dd>{{ $resident->occupation ?: '—' }}</dd>
                        <dt>Status Kawin</dt><dd>{{ $resident->marital_status ?: '—' }}</dd>
                        <dt>Orang Tua</dt><dd>Ayah: {{ $resident->father_name ?: '—' }} · Ibu: {{ $resident->mother_name ?: '—' }}</dd>
                        <dt>Kontak</dt><dd>{{ $resident->phone ?: '—' }} · {{ $resident->email ?: '—' }}</dd>
                    </dl>
                </div>
                <div class="tab-pane" id="history">
                    <ul class="timeline timeline-inverse">
                        @forelse($resident->events as $event)
                        <li><i class="fa {{ in_array($event->event_type,['birth','arrival','reactivated'])?'fa-plus bg-green':'fa-minus bg-red' }}"></i><div class="timeline-item"><span class="time"><i class="fa fa-calendar"></i> {{ $event->event_date->format('d-m-Y') }}</span><h3 class="timeline-header"><strong>{{ $event->event_label }}</strong></h3><div class="timeline-body">{{ $event->destination_address ?: $event->cause ?: $event->notes ?: 'Tidak ada catatan tambahan.' }}</div></div></li>
                        @empty<li><i class="fa fa-info bg-gray"></i><div class="timeline-item"><div class="timeline-body">Belum ada riwayat peristiwa.</div></div></li>@endforelse
                        <li><i class="fa fa-clock-o bg-gray"></i></li>
                    </ul>
                </div>
                <div class="tab-pane" id="groups">
                    <table class="table table-striped"><thead><tr><th>Kelompok</th><th>Kategori</th><th>Jabatan</th></tr></thead><tbody>@forelse($resident->groupMemberships as $membership)<tr><td><a href="{{ route('admin.population.groups.show',$membership->group) }}">{{ $membership->group->name }}</a></td><td>{{ $membership->group->category }}</td><td>{{ $membership->position }}</td></tr>@empty<tr><td colspan="3" class="empty-state">Belum menjadi anggota kelompok.</td></tr>@endforelse</tbody></table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
