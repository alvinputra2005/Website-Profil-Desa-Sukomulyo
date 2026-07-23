@extends('layouts.admin')
@section('title','Laporan Penduduk')
@section('page-description','Laporan kependudukan bulanan')
@section('content')
<div class="box box-info population-report">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text"></i> Laporan {{ $start->translatedFormat('F Y') }}</h3><div class="box-tools"><a href="{{ route('admin.population.report.export',['year'=>$year,'month'=>$month]) }}" class="btn btn-success btn-sm" data-no-ajax><i class="fa fa-file-excel-o"></i> Unduh CSV</a> <button onclick="window.print()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Cetak</button></div></div>
    <div class="box-body">
        <form method="get" class="population-period-filter">
            <div><label>Bulan</label><select name="month" class="form-control">@foreach(range(1,12) as $number)<option value="{{ $number }}" @selected($month===$number)>{{ \Carbon\Carbon::create(null,$number)->translatedFormat('F') }}</option>@endforeach</select></div>
            <div><label>Tahun</label><select name="year" class="form-control">@foreach(range(now()->year, max(1900,now()->year-10)) as $number)<option @selected($year===$number)>{{ $number }}</option>@endforeach</select></div>
            <button class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
        </form>
        <div class="population-report-heading"><h3>Laporan Kependudukan Bulanan</h3><p>DESA SUKOMULYO · {{ strtoupper($start->translatedFormat('F Y')) }}</p></div>
        <div class="table-responsive"><table class="table table-bordered population-report-table">
            <thead><tr><th>Uraian</th><th>Laki-laki</th><th>Perempuan</th><th>Jumlah</th></tr></thead>
            <tbody>
                <tr><th>Penduduk awal bulan</th><td>{{ $beginning['male'] }}</td><td>{{ $beginning['female'] }}</td><td>{{ $beginning['total'] }}</td></tr>
                @foreach(['birth'=>'Lahir','arrival'=>'Datang','departure'=>'Pindah','death'=>'Meninggal'] as $type=>$label)<tr><td>{{ $label }}</td><td>{{ $changes[$type]['male'] }}</td><td>{{ $changes[$type]['female'] }}</td><td>{{ $changes[$type]['total'] }}</td></tr>@endforeach
                <tr class="report-ending"><th>Penduduk akhir bulan</th><th>{{ $ending['male'] }}</th><th>{{ $ending['female'] }}</th><th>{{ $ending['total'] }}</th></tr>
            </tbody>
        </table></div>
        <h4 class="report-detail-title">Rincian Peristiwa Penduduk</h4>
        <div class="table-responsive"><table class="table table-striped table-bordered">
            <thead><tr><th>No</th><th>Tanggal</th><th>Peristiwa</th><th>NIK</th><th>Nama</th><th>JK</th><th>Keterangan</th></tr></thead>
            <tbody>@forelse($events as $event)<tr><td>{{ $loop->iteration }}</td><td>{{ $event->event_date->format('d-m-Y') }}</td><td><span class="label {{ in_array($event->event_type,['birth','arrival'])?'label-success':'label-default' }}">{{ $event->event_label }}</span></td><td>{{ $event->nik }}</td><td>{{ $event->resident_name }}</td><td>{{ $event->sex }}</td><td>{{ $event->destination_address ?: $event->cause ?: $event->notes ?: '—' }}</td></tr>@empty<tr><td colspan="7" class="empty-state">Tidak ada peristiwa penduduk pada periode ini.</td></tr>@endforelse</tbody>
        </table></div>
    </div>
</div>
@endsection
