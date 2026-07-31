@php($prefix = 'programs['.$index.']')
<div class="apbdes-line-row apbdes-program-row" data-apbdes-row>
    <div class="apbdes-line-row__heading">
        <strong data-row-title>{{ $row['name'] ?? 'Program/kegiatan' }}</strong>
        <button type="button" class="btn btn-xs btn-danger" data-remove-row title="Hapus program">
            <i class="fa fa-trash"></i>
        </button>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <div class="form-group">
                <label>Kode</label>
                <input name="{{ $prefix }}[code]" class="form-control input-sm" maxlength="50"
                       value="{{ $row['code'] ?? '' }}" placeholder="2.1.01">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Bidang Belanja</label>
                <select name="{{ $prefix }}[category_code]" class="form-control input-sm" data-program-category>
                    <option value="">Pilih bidang</option>
                    @foreach ($spendingCategories as $code => $label)
                        <option value="{{ $code }}" @selected(($row['category_code'] ?? '') === $code)>{{ $code }} — {{ $label }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="{{ $prefix }}[category]" value="{{ $row['category'] ?? '' }}" data-program-category-label>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Program/Kegiatan</label>
                <input name="{{ $prefix }}[name]" class="form-control input-sm" maxlength="255"
                       value="{{ $row['name'] ?? '' }}" data-row-name>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>Uraian/Output Kegiatan</label>
        <textarea name="{{ $prefix }}[description]" class="form-control input-sm" rows="2" maxlength="2000">{{ $row['description'] ?? '' }}</textarea>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Anggaran (Rp)</label>
                <input type="number" name="{{ $prefix }}[budget]" class="form-control input-sm"
                       min="0" step="0.01" value="{{ $row['budget'] ?? 0 }}" data-money-input>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Realisasi (Rp)</label>
                <input type="number" name="{{ $prefix }}[realization]" class="form-control input-sm"
                       min="0" step="0.01" value="{{ $row['realization'] ?? 0 }}" data-money-input>
            </div>
        </div>
    </div>
</div>
