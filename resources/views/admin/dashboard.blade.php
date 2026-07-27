@extends('layouts.admin')
@section('title','Beranda')
@section('page-title','Beranda')
@section('page-description','Ringkasan Sistem Informasi Desa')
@section('content')
<div class="row dashboard-stats">
    @foreach($stats as $stat)
    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
        <div class="small-box {{ $stat['color'] }}">
            <div class="inner">
                <h3>{{ number_format($stat['value'], 0, ',', '.') }}</h3>
                <p>{{ $stat['label'] }}</p>
            </div>
            <div class="icon"><i class="fa {{ $stat['icon'] }}"></i></div>
        </div>
    </div>
    @endforeach
</div>
<div class="dashboard-statistics">
    @foreach($statisticPanels as $panel)
    @php($panelTotal=collect($panel['items'])->sum('value'))
    @php($panelMax=max(collect($panel['items'])->max('value')??0,1))
    <section class="box dashboard-chart-card">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa {{ $panel['icon'] }}"></i> {{ $panel['title'] }}</h3><span class="chart-total">Total {{ number_format($panelTotal,0,',','.') }} {{ $panel['unit'] }}</span>
        </div>
        <div class="box-body statistic-bars">
            @foreach($panel['items'] as $item)
            @php($percentage=$panelTotal>0?round(($item['value']/$panelTotal)*100,1):0)
            <div class="statistic-bar-item">
                <div class="statistic-bar-label"><span>{{ $item['label'] }}</span><strong>{{ number_format($item['value'],0,',','.') }} <small>{{ $panel['unit'] }}</small></strong></div>
                <div class="statistic-bar-track"><span style="width: {{ min(100,($item['value']/$panelMax)*100) }}%"></span></div>
                <small class="statistic-percentage">{{ number_format($percentage,1,',','.') }}%</small>
            </div>
            @endforeach
        </div>
    </section>
    @endforeach
</div>
<div class="row">
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-envelope"></i> Pesan Terbaru</h3>
                <div class="box-tools"><a href="{{ route('admin.messages.index') }}" class="btn btn-box-tool">Lihat Semua</a></div>
            </div>
            <div class="box-body no-padding">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pengirim</th>
                                <th>Pesan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>@forelse($messages as $message)<tr>
                                <td><a href="{{ route('admin.messages.show',$message) }}"><strong>{{ $message->name }}</strong></a><br><small>{{ $message->email }}</small></td>
                                <td>{{ Str::limit($message->message,70) }}</td>
                                <td><span class="label label-info">{{ $message->created_at->diffForHumans() }}</span></td>
                            </tr>@empty<tr>
                                <td colspan="3" class="empty-state">Belum ada pesan masuk.</td>
                            </tr>@endforelse</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">

        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> Aktivitas Hari Ini</h3>
            </div>
            <div class="box-body">@forelse($activities as $activity)<div class="post">
                    <div class="user-block"><span class="img-circle img-bordered-sm bg-maroon" style="float:left;width:40px;height:40px;line-height:40px;text-align:center;color:#fff">{{ strtoupper(substr($activity->user?->name??'S',0,1)) }}</span><span class="username">{{ $activity->user?->name??'Sistem' }}</span><span class="description">{{ $activity->created_at->diffForHumans() }}</span></div>
                    <p>{{ $activity->description }}</p>
                </div>@empty<p class="text-muted">Belum ada aktivitas hari ini.</p>@endforelse</div>@if($activities->hasPages())<div class="box-footer clearfix">{{ $activities->onEachSide(1)->links() }}</div>@endif
        </div>
        
    </div>
</div>
@endsection
