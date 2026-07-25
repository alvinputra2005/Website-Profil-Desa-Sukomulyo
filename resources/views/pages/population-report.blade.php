<x-layouts.app title="Laporan Penduduk" description="Laporan agregat perkembangan penduduk Desa Sukomulyo.">
    <x-page-header title="Laporan Penduduk" description="Rekapitulasi agregat penduduk Desa Sukomulyo per bulan." />
    <div class="container">
        <div id="sc_innerpage_wrap"><section class="sc_innerpage_contentbx fullwidth">
            <form method="get" class="public-period-filter">
                <label>Bulan <select name="month">@foreach(range(1,12) as $number)<option value="{{ $number }}" @selected($month===$number)>{{ \Carbon\Carbon::create(null,$number)->translatedFormat('F') }}</option>@endforeach</select></label>
                <label>Tahun <select name="year">@foreach(range(now()->year, max(1900,now()->year-10)) as $number)<option @selected($year===$number)>{{ $number }}</option>@endforeach</select></label>
                <button type="submit">Tampilkan</button>
            </form>
            <div class="public-report-heading"><span class="section-kicker">Kependudukan</span><h2>Ringkasan {{ $start->translatedFormat('F Y') }}</h2><p>Data berikut bersifat agregat dan tidak menampilkan identitas pribadi penduduk.</p></div>
            <div class="public-report-grid">
                @foreach([['Penduduk awal bulan',$beginning['total'],'fas fa-users'],['Lahir + Datang',$changes['birth']['total']+$changes['arrival']['total'],'fas fa-plus-circle'],['Pindah + Meninggal',$changes['departure']['total']+$changes['death']['total'],'fas fa-minus-circle'],['Penduduk akhir bulan',$ending['total'],'fas fa-chart-line']] as [$label,$value,$icon])
                    <article><i class="{{ $icon }}"></i><strong>{{ number_format($value,0,',','.') }}</strong><span>{{ $label }}</span></article>
                @endforeach
            </div>
            <div class="data-panel public-report-table"><h3>Komposisi Penduduk Akhir Bulan</h3><table><thead><tr><th></th><th>Laki-laki</th><th>Perempuan</th><th>Total</th></tr></thead><tbody><tr><th>Jumlah</th><td>{{ number_format($ending['male'],0,',','.') }}</td><td>{{ number_format($ending['female'],0,',','.') }}</td><td>{{ number_format($ending['total'],0,',','.') }}</td></tr></tbody></table></div>
        </section></div>
    </div>
</x-layouts.app>
