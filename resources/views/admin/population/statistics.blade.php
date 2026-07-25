@extends('layouts.admin')
@section('title','Statistik Kependudukan')
@section('page-description','Rekap data penduduk aktif')
@section('content')
<div class="row dashboard-stats population-summary">
    @foreach([['Penduduk',$summary['residents'],'fa-users','bg-aqua'],['Laki-laki',$summary['male'],'fa-male','bg-green'],['Perempuan',$summary['female'],'fa-female','bg-yellow'],['Keluarga',$summary['families'],'fa-id-card','bg-red']] as [$label,$value,$icon,$color])
    <div class="col-md-3 col-sm-6"><div class="small-box {{ $color }}"><div class="inner"><h3>{{ number_format($value,0,',','.') }}</h3><p>{{ $label }}</p></div><div class="icon"><i class="fa {{ $icon }}"></i></div></div></div>
    @endforeach
</div>
<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> Statistik Menurut {{ $categories[$category] }}</h3><div class="box-tools"><button type="button" onclick="window.print()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Cetak</button></div></div>
    <div class="box-body">
        <form method="get" class="population-stat-selector"><label>Jenis Statistik</label><select name="category" class="form-control select2" onchange="this.form.submit()">@foreach($categories as $value=>$label)<option value="{{ $value }}" @selected($category===$value)>{{ $label }}</option>@endforeach</select></form>
        <div class="table-responsive"><table class="table table-bordered table-striped population-stat-table">
            <thead><tr><th rowspan="2">No</th><th rowspan="2">{{ $categories[$category] }}</th><th colspan="2">Jumlah</th><th colspan="2">Laki-laki</th><th colspan="2">Perempuan</th></tr><tr><th>Total</th><th>Persen</th><th>Total</th><th>Persen</th><th>Total</th><th>Persen</th></tr></thead>
            <tbody>@forelse($rows as $row)<tr><td>{{ $loop->iteration }}</td><td>{{ $row['label'] }}</td><td class="text-right">{{ number_format($row['total'],0,',','.') }}</td><td class="text-right">{{ number_format($row['percentage'],2,',','.') }}%</td><td class="text-right">{{ number_format($row['male'],0,',','.') }}</td><td class="text-right">{{ $summary['male']?number_format($row['male']/$summary['male']*100,2,',','.'):'0,00' }}%</td><td class="text-right">{{ number_format($row['female'],0,',','.') }}</td><td class="text-right">{{ $summary['female']?number_format($row['female']/$summary['female']*100,2,',','.'):'0,00' }}%</td></tr>@empty<tr><td colspan="8" class="empty-state">Belum ada data penduduk aktif.</td></tr>@endforelse</tbody>
            <tfoot><tr><th></th><th>TOTAL</th><th class="text-right">{{ number_format($summary['residents'],0,',','.') }}</th><th class="text-right">100%</th><th class="text-right">{{ number_format($summary['male'],0,',','.') }}</th><th class="text-right">{{ $summary['male']?'100%':'0%' }}</th><th class="text-right">{{ number_format($summary['female'],0,',','.') }}</th><th class="text-right">{{ $summary['female']?'100%':'0%' }}</th></tr></tfoot>
        </table></div>
    </div>
</div>
@endsection
