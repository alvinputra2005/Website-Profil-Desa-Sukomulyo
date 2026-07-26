@props([
    'name',
    'label',
    'media' => collect(),
    'selected' => null,
    'required' => false,
    'showLabel' => true,
    'showFieldError' => true,
])
@php
    $uploadBase = str_ends_with($name, '_id') ? substr($name, 0, -3) : $name;
    $selectedId = old($name, $selected?->id);
    $selectedMedia = $media->firstWhere('id', (int) $selectedId) ?? $selected;
    $alt = old($uploadBase.'_alt', $selectedMedia?->alt_text);
@endphp
<div class="image-picker {{ $errors->has($name) || $errors->has($uploadBase.'_upload') ? 'has-error' : '' }}" data-image-picker>
    @if($showLabel)
        <label for="{{ $name }}" class="control-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
    @endif
    <div class="image-picker-preview" data-image-preview>
        @if($selectedMedia)
            <img src="{{ $selectedMedia->url }}" alt="{{ $alt }}">
            <button type="button" class="image-picker-remove" data-image-clear data-toggle="tooltip" title="Hapus gambar" aria-label="Hapus gambar">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        @else
            <i class="fa fa-picture-o" aria-hidden="true"></i>
            <span>Belum ada gambar</span>
        @endif
    </div>
    <input type="hidden" id="{{ $name }}" name="{{ $name }}" value="{{ $selectedId }}" data-image-select>
    <div class="form-group image-picker-control">
        <label for="{{ $uploadBase }}_upload">Upload gambar baru</label>
        <input type="file" id="{{ $uploadBase }}_upload" name="{{ $uploadBase }}_upload" class="form-control" accept="image/jpeg,image/png,image/webp" data-image-upload>
        <span class="help-block">JPG, PNG, atau WebP, maksimal 5 MB. Gambar otomatis dioptimalkan.</span>
    </div>
    <div class="form-group image-picker-control">
        <label for="{{ $uploadBase }}_alt">Alt text</label>
        <input id="{{ $uploadBase }}_alt" name="{{ $uploadBase }}_alt" class="form-control" maxlength="255" value="{{ $alt }}" placeholder="Contoh: Kegiatan warga Desa Sukomulyo" data-image-alt>
    </div>
    <input type="hidden" name="remove_{{ $uploadBase }}" value="0" data-image-remove>
    @if($showFieldError)
        @error($name)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
    @endif
    @error($uploadBase.'_upload')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
</div>
