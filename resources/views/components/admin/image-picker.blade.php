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
        @else
            <i class="fa fa-picture-o" aria-hidden="true"></i>
            <span>Belum ada gambar</span>
        @endif
    </div>
    <div class="form-group image-picker-control">
        <label for="{{ $name }}">Pilih dari Media Library</label>
        <select id="{{ $name }}" name="{{ $name }}" class="form-control select2" data-image-select>
            <option value="" data-url="" data-alt="">-- Tidak dipilih --</option>
            @foreach($media as $image)
                <option value="{{ $image->id }}" data-url="{{ $image->url }}" data-alt="{{ $image->alt_text }}" @selected((string) $selectedId === (string) $image->id)>{{ $image->original_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group image-picker-control">
        <label for="{{ $uploadBase }}_upload">Atau upload gambar baru</label>
        <input type="file" id="{{ $uploadBase }}_upload" name="{{ $uploadBase }}_upload" class="form-control" accept="image/jpeg,image/png,image/webp" data-image-upload>
        <span class="help-block">JPG, PNG, atau WebP, maksimal 5 MB. Gambar otomatis dioptimalkan.</span>
    </div>
    <div class="form-group image-picker-control">
        <label for="{{ $uploadBase }}_alt">Alt text</label>
        <input id="{{ $uploadBase }}_alt" name="{{ $uploadBase }}_alt" class="form-control" maxlength="255" value="{{ $alt }}" placeholder="Contoh: Kegiatan warga Desa Sukomulyo" data-image-alt>
    </div>
    <input type="hidden" name="remove_{{ $uploadBase }}" value="0" data-image-remove>
    <button type="button" class="btn btn-default btn-sm" data-image-clear><i class="fa fa-times"></i> Hapus gambar</button>
    @if($showFieldError)
        @error($name)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
    @endif
    @error($uploadBase.'_upload')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
</div>
