@extends('layouts.admin')

@section('title', 'Statistik Penduduk Terkini')
@section('page-description', 'Statistik Kependudukan dinamis berdasarkan data warga yang telah tercatat')

@section('content')
<div class="box box-default">
    <div class="box-body">
        <form method="get" action="{{ route('admin.statistics.population.show') }}" class="statistics-category-filters">
            <div class="form-group">
                <label for="population-indicator">Pilih Indikator</label>
                <select id="population-indicator" name="indicator" class="form-control select2" style="width: 100%">
                    @foreach ($indicators as $indicator)
                        <option value="{{ $indicator->key }}" @selected($selectedIndicator->is($indicator))>
                            {{ $indicator->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="statistics-category-filter-action">
                <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="row dashboard-stats population-summary">
    @foreach ([
        ['Total Penduduk Terklasifikasi', $result['total'], 'fa-users', 'bg-aqua'],
        ['Data Terklasifikasi', $result['classified'], 'fa-check-circle', 'bg-green'],
    ] as [$label, $value, $icon, $color])
        <div class="col-md-3 col-sm-6">
            <div class="small-box {{ $color }}">
                <div class="inner"><h3>{{ number_format($value, 0, ',', '.') }}</h3><p>{{ $label }} ({{ $result['indicator']['unit'] }})</p></div>
                <div class="icon"><i class="fa {{ $icon }}"></i></div>
            </div>
        </div>
    @endforeach
</div>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Statistik {{ $selectedIndicator->label }}</h3>
        <div class="box-tools"><button type="button" onclick="window.print()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Cetak</button></div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped population-stat-table">
                <thead><tr><th>No</th><th>Kategori</th><th>Jumlah</th><th>Persentase</th></tr></thead>
                <tbody>
                    @forelse ($result['items'] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['label'] }}</td>
                            <td class="text-right">{{ number_format($item['value'], 0, ',', '.') }} {{ $result['indicator']['unit'] }}</td>
                            <td class="text-right">{{ number_format($item['percentage'], 2, ',', '.') }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">Belum ada data yang dapat diklasifikasikan.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr><th></th><th>TOTAL</th><th class="text-right">{{ number_format($result['total'], 0, ',', '.') }} {{ $result['indicator']['unit'] }}</th><th class="text-right">{{ $result['total'] > 0 ? '100%' : '0%' }}</th></tr></tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
