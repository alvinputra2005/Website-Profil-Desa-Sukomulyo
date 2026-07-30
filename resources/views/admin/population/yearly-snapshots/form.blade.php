@extends('layouts.admin')
@section('title',$snapshot->exists ? 'Ubah Statistik Tahunan' : 'Tambah Statistik Tahunan')
@section('page-description','Rekap penduduk berdasarkan tahun dan jenis kelamin')
@section('content')
<form method="post"
      action="{{ $snapshot->exists ? route('admin.population.yearly-snapshots.update', $snapshot) : route('admin.population.yearly-snapshots.store') }}"
      data-dirty-form
      data-yearly-snapshot-form>
    @csrf
    @if ($snapshot->exists) @method('put') @endif
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Form Snapshot Penduduk</h3></div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="required" for="snapshot-year">Tahun</label>
                        <input id="snapshot-year" type="number" name="year" class="form-control"
                               min="1900" max="{{ config('village.population_year') }}"
                               value="{{ old('year', $snapshot->year) }}" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="required" for="snapshot-male">Jumlah Laki-laki</label>
                                <input id="snapshot-male" type="number" name="male_count" class="form-control"
                                       min="0" value="{{ old('male_count', $snapshot->male_count ?? 0) }}"
                                       data-snapshot-male required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="required" for="snapshot-female">Jumlah Perempuan</label>
                                <input id="snapshot-female" type="number" name="female_count" class="form-control"
                                       min="0" value="{{ old('female_count', $snapshot->female_count ?? 0) }}"
                                       data-snapshot-female required>
                            </div>
                        </div>
                    </div>
                    <div class="callout callout-info" aria-live="polite">
                        <h4>Pratinjau Perhitungan</h4>
                        <p>Total: <strong data-snapshot-total>0 jiwa</strong></p>
                        <p>Laki-laki: <strong data-snapshot-male-percentage>0%</strong> · Perempuan: <strong data-snapshot-female-percentage>0%</strong></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="snapshot-reference-date">Tanggal Referensi</label>
                        <input id="snapshot-reference-date" type="date" name="reference_date" class="form-control"
                               value="{{ old('reference_date', $snapshot->reference_date?->format('Y-m-d')) }}">
                        <p class="help-block">Contoh: tanggal rekapitulasi per 31 Desember.</p>
                    </div>
                    <div class="form-group">
                        <label for="snapshot-source">Sumber</label>
                        <input id="snapshot-source" name="source" class="form-control" maxlength="150"
                               value="{{ old('source', $snapshot->source) }}"
                               placeholder="Administrasi Kependudukan Desa">
                    </div>
                    <div class="form-group">
                        <label for="snapshot-notes">Catatan</label>
                        <textarea id="snapshot-notes" name="notes" class="form-control" rows="4">{{ old('notes', $snapshot->notes) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input name="is_published" value="1" type="checkbox" @checked(old('is_published', $snapshot->is_published))>
                            Publikasikan pada halaman statistik
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <a href="{{ route('admin.population.yearly-snapshots.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan Snapshot</button>
        </div>
    </div>
</form>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-yearly-snapshot-form]');
    if (!form) return;
    var male = form.querySelector('[data-snapshot-male]');
    var female = form.querySelector('[data-snapshot-female]');
    var totalOutput = form.querySelector('[data-snapshot-total]');
    var maleOutput = form.querySelector('[data-snapshot-male-percentage]');
    var femaleOutput = form.querySelector('[data-snapshot-female-percentage]');
    var number = new Intl.NumberFormat('id-ID');
    var percentage = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
    var update = function () {
        var maleValue = Math.max(0, Number(male.value) || 0);
        var femaleValue = Math.max(0, Number(female.value) || 0);
        var total = maleValue + femaleValue;
        totalOutput.textContent = number.format(total) + ' jiwa';
        maleOutput.textContent = percentage.format(total ? maleValue / total * 100 : 0) + '%';
        femaleOutput.textContent = percentage.format(total ? femaleValue / total * 100 : 0) + '%';
    };
    male.addEventListener('input', update);
    female.addEventListener('input', update);
    update();
});
</script>
@endpush
@endsection
