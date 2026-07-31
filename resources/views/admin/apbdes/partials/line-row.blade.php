@php
    $prefix = $type.'['.$index.']';
    $isSpending = $type === 'spending';
@endphp
<div class="apbdes-line-row" data-apbdes-row>
    <div class="apbdes-line-row__heading">
        <strong data-row-title>{{ $row['name'] ?? ($isSpending ? 'Bidang belanja' : 'Sumber pendapatan') }}</strong>
        <button type="button" class="btn btn-xs btn-danger" data-remove-row title="Hapus baris">
            <i class="fa fa-trash"></i>
        </button>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <div class="form-group">
                <label>Kode</label>
                <input name="{{ $prefix }}[code]" class="form-control input-sm" maxlength="30"
                       value="{{ $row['code'] ?? '' }}" placeholder="{{ $isSpending ? '2.1' : '1.1' }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="required">Uraian</label>
                <input name="{{ $prefix }}[name]" class="form-control input-sm" maxlength="255"
                       value="{{ $row['name'] ?? '' }}" required data-row-name>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Nama Singkat</label>
                <input name="{{ $prefix }}[short_name]" class="form-control input-sm" maxlength="100"
                       value="{{ $row['short_name'] ?? '' }}" placeholder="Label grafik">
            </div>
        </div>
    </div>
    @if ($isSpending)
        <div class="form-group">
            <label>Deskripsi Bidang</label>
            <textarea name="{{ $prefix }}[description]" class="form-control input-sm" rows="2" maxlength="2000">{{ $row['description'] ?? '' }}</textarea>
        </div>
    @endif
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label class="required">{{ $isSpending ? 'Anggaran' : 'Target' }} (Rp)</label>
                <input type="number" name="{{ $prefix }}[budget]" class="form-control input-sm"
                       min="0" step="0.01" value="{{ $row['budget'] ?? 0 }}" required data-line-budget data-money-input>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label class="required">Realisasi (Rp)</label>
                <input type="number" name="{{ $prefix }}[realization]" class="form-control input-sm"
                       min="0" step="0.01" value="{{ $row['realization'] ?? 0 }}" required data-line-realization data-money-input>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Catatan/Koreksi Sumber</label>
                <input name="{{ $prefix }}[note]" class="form-control input-sm" maxlength="2000"
                       value="{{ $row['note'] ?? '' }}" placeholder="Opsional">
            </div>
        </div>
    </div>
</div>
