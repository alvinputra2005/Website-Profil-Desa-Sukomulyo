@props(['name', 'value' => '', 'placeholder' => 'Mulai tulis konten di sini...'])
<div class="article-editor" data-article-editor data-upload-url="{{ route('admin.media.editor-upload') }}">
    <div class="article-editor-toolbar" role="toolbar" aria-label="Pemformatan konten">
        <button type="button" data-command="undo" title="Urungkan"><i class="fa fa-undo"></i></button>
        <button type="button" data-command="redo" title="Ulangi"><i class="fa fa-repeat"></i></button><span></span>
        <button type="button" data-command="bold" title="Tebal"><i class="fa fa-bold"></i></button>
        <button type="button" data-command="italic" title="Miring"><i class="fa fa-italic"></i></button>
        <button type="button" data-command="underline" title="Garis bawah"><i class="fa fa-underline"></i></button><span></span>
        <button type="button" data-command="formatBlock" data-value="h2" title="Judul besar">H2</button>
        <button type="button" data-command="formatBlock" data-value="h3" title="Judul kecil">H3</button>
        <button type="button" data-command="formatBlock" data-value="p" title="Paragraf">P</button><span></span>
        <button type="button" data-command="insertUnorderedList" title="Daftar poin"><i class="fa fa-list-ul"></i></button>
        <button type="button" data-command="insertOrderedList" title="Daftar nomor"><i class="fa fa-list-ol"></i></button>
        <button type="button" data-command="formatBlock" data-value="blockquote" title="Kutipan"><i class="fa fa-quote-right"></i></button><span></span>
        <button type="button" data-action="link" title="Tautan"><i class="fa fa-link"></i></button>
        <button type="button" data-action="image" title="Sisipkan gambar"><i class="fa fa-image"></i></button>
        <button type="button" data-action="image-align" data-align="left" title="Gambar kiri"><i class="fa fa-align-left"></i></button>
        <button type="button" data-action="image-align" data-align="center" title="Gambar tengah"><i class="fa fa-align-center"></i></button>
        <button type="button" data-action="image-align" data-align="right" title="Gambar kanan"><i class="fa fa-align-right"></i></button>
        <button type="button" data-action="image-align" data-align="full" title="Gambar lebar penuh"><i class="fa fa-arrows-h"></i></button><span></span>
        <button type="button" data-command="removeFormat" title="Hapus format"><i class="fa fa-eraser"></i></button>
        <input type="file" data-image-input accept="image/jpeg,image/png,image/webp" hidden>
    </div>
    <div class="article-editor-status" data-editor-status hidden></div>
    <div class="article-editor-surface" contenteditable="true" data-placeholder="{{ $placeholder }}">{!! app(\App\Services\HtmlSanitizer::class)->clean((string) $value) !!}</div>
    <textarea hidden id="{{ $name }}" name="{{ $name }}">{{ $value }}</textarea>
</div>
