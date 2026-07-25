@props(['name', 'value' => '', 'placeholder' => 'Mulai tulis konten di sini...'])
@php($cleanValue = app(\App\Services\HtmlSanitizer::class)->clean((string) $value))

<div class="article-editor" data-tinymce-upload-url="{{ route('admin.media.editor-upload') }}">
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        data-tinymce
        data-placeholder="{{ $placeholder }}"
        aria-label="Isi artikel"
    >{{ $cleanValue }}</textarea>
</div>
