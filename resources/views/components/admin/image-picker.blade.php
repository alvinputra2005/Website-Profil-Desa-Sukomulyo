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

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-image-picker]').forEach(function (picker) {
                    const select = picker.querySelector('[data-image-select]');
                    const upload = picker.querySelector('[data-image-upload]');
                    const preview = picker.querySelector('[data-image-preview]');
                    const alt = picker.querySelector('[data-image-alt]');
                    const remove = picker.querySelector('[data-image-remove]');
                    const empty = function () {
                        preview.innerHTML = '<i class="fa fa-picture-o" aria-hidden="true"></i><span>Belum ada gambar</span>';
                    };
                    const show = function (url) {
                        if (!url) {
                            empty();
                            return;
                        }
                        preview.innerHTML = '';
                        const image = document.createElement('img');
                        image.src = url;
                        image.alt = alt.value;
                        preview.appendChild(image);
                    };
                    select?.addEventListener('change', function () {
                        const option = select.options[select.selectedIndex];
                        remove.value = '0';
                        upload.value = '';
                        if (option?.dataset.url) {
                            show(option.dataset.url);
                            alt.value = option.dataset.alt || '';
                        } else {
                            empty();
                        }
                    });
                    upload?.addEventListener('change', function () {
                        const file = upload.files?.[0];
                        if (!file) return;
                        select.value = '';
                        if (window.jQuery) window.jQuery(select).trigger('change.select2');
                        remove.value = '0';
                        show(URL.createObjectURL(file));
                        if (!alt.value) alt.value = file.name.replace(/\.[^/.]+$/, '');
                    });
                    alt?.addEventListener('input', function () {
                        const image = preview.querySelector('img');
                        if (image) image.alt = alt.value;
                    });
                    picker.querySelector('[data-image-clear]')?.addEventListener('click', function () {
                        select.value = '';
                        if (window.jQuery) window.jQuery(select).trigger('change.select2');
                        upload.value = '';
                        alt.value = '';
                        remove.value = '1';
                        empty();
                    });
                });
            });
        </script>
    @endpush
@endonce
