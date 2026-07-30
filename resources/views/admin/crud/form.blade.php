@extends('layouts.admin')
@section('title',($item->exists?'Ubah ':'Tambah ').$config['title'])
@section('page-description','Lengkapi formulir berikut')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $item->exists?route('admin.resources.update',[$resource,$item]):route('admin.resources.store',$resource) }}" data-dirty-form>
@csrf @if($item->exists)@method('put')@endif
<div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Form {{ $config['title'] }}</h3></div><div class="box-body">
@foreach($config['fields'] as $name=>$field)
@if($name !== 'slug')
@php
    $type=$field['type']??'text';
    $value=old($name,request($name,data_get($item,$name)));
    if($value instanceof \Carbon\CarbonInterface) $value=$type==='date'?$value->format('Y-m-d'):$value->format('Y-m-d\TH:i');
    if(is_array($value)) $value=json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
@endphp
<div class="form-group {{ $errors->has($name)?'has-error':'' }}"><label for="{{ $name }}" class="control-label {{ str_contains($field['rules']??'','required')?'required':'' }}">{{ $field['label'] }}</label>
@if($type==='select')
<select id="{{ $name }}" name="{{ $name }}" class="form-control select2">@foreach($field['options'] as $option=>$label)<option value="{{ $option }}" @selected((string)$value===(string)$option)>{{ $label }}</option>@endforeach</select>
@elseif($type==='relation')
<select id="{{ $name }}" name="{{ $name }}" class="form-control select2"><option value="">-- Pilih --</option>@foreach($relations[$name] as $relation)<option value="{{ $relation->id }}" @selected((string)$value===(string)$relation->id)>{{ $relation->{$field['display']} }}</option>@endforeach</select>
@elseif($type==='toggle')
<label class="checkbox-inline"><input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked((bool)$value)> Ya / Aktif</label>
@elseif($type==='textarea')
<textarea id="{{ $name }}" name="{{ $name }}" class="form-control" rows="5" @required(str_contains($field['rules']??'','required'))>{{ $value }}</textarea>
@elseif($type==='editor')
<x-admin.rich-editor :name="$name" :value="$value" />
@elseif($type==='image')
@if($resource==='galleries' && $name==='cover_media_id')
<div class="gallery-manager" data-gallery-manager>
    <input type="file" name="gallery_item_uploads[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple data-gallery-files>
    <p class="help-block">Maksimal 5 gambar per sekali simpan · JPG, PNG, atau WebP · maksimal 5 MB per gambar. Foto pertama menjadi sampul; seret untuk mengurutkan atau tekan bintang untuk menjadikannya sampul.</p>
    <p class="help-block" data-gallery-upload-status role="status" aria-live="polite" hidden></p>
    @error('gallery_item_uploads')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
    @error('gallery_item_uploads.*')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
    <div class="gallery-photo-grid" data-gallery-photo-grid>
        @foreach($galleryItems as $galleryItem)
        <div class="gallery-photo-card" draggable="true" data-gallery-photo-card data-existing-photo>
            <img src="{{ $galleryItem->media?->thumbnail_url ?: $galleryItem->media?->url }}" alt="{{ $galleryItem->media?->alt_text }}">
            <button type="button" class="gallery-photo-cover" data-gallery-cover title="Jadikan sampul" aria-label="Jadikan sampul"><i class="fa fa-star"></i></button>
            <button type="button" class="gallery-photo-remove" data-gallery-remove title="Hapus gambar" aria-label="Hapus gambar">&times;</button>
            <span class="gallery-photo-drag"><i class="fa fa-bars"></i></span>
            <input type="hidden" name="gallery_items[{{ $galleryItem->id }}][display_order]" value="{{ $galleryItem->display_order }}" data-gallery-order>
            <input type="hidden" name="gallery_items[{{ $galleryItem->id }}][caption]" value="{{ $galleryItem->caption }}">
            <input type="hidden" name="gallery_sequence[]" value="existing:{{ $galleryItem->id }}" data-gallery-sequence>
            <input type="checkbox" name="remove_gallery_items[]" value="{{ $galleryItem->id }}" data-gallery-remove-input hidden>
        </div>
        @endforeach
        <button type="button" class="gallery-photo-add" data-gallery-add><i class="fa fa-plus"></i><span>Tambah gambar</span></button>
    </div>
</div>
@else
@php($selectedMedia=isset($field['relation']) ? data_get($item,$field['relation']) : null)
<x-admin.image-picker :name="$name" :label="$field['label']" :media="$media ?? collect()" :selected="$selectedMedia" :required="str_contains($field['rules']??'','required')" :show-label="false" :show-field-error="false" />
@endif
@else
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" class="form-control" @if(isset($field['step']))step="{{ $field['step'] }}"@endif>
@endif
@error($name)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
</div>
@endif
@endforeach
@if($resource==='publications')
<div class="publication-attachment-manager" data-publication-attachments>
    <hr>
    <h4><i class="fa fa-file-pdf-o text-red"></i> Lampiran PDF</h4>
    <p class="help-block">Unggah maksimal 10 PDF per sekali simpan, masing-masing maksimal 10 MB. Lampiran pertama menjadi PDF utama pada daftar pengumuman.</p>
    <div class="form-group {{ $errors->has('attachment_uploads.*')?'has-error':'' }}">
        <label for="attachment_uploads">Tambah lampiran PDF</label>
        <input id="attachment_uploads" type="file" name="attachment_uploads[]" class="form-control" accept="application/pdf,.pdf" multiple data-attachment-files>
        @error('attachment_uploads')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
        @error('attachment_uploads.*')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
    </div>
    <div class="publication-attachment-list" data-attachment-list>
        @foreach($publicationAttachments as $attachment)
        <div class="publication-attachment-row" data-attachment-row data-existing-attachment>
            <span class="publication-attachment-handle" title="Urutkan lampiran"><i class="fa fa-bars"></i></span>
            <i class="fa fa-file-pdf-o text-red publication-attachment-file-icon"></i>
            <div class="publication-attachment-fields">
                <strong>{{ $attachment->media?->original_name }}</strong>
                <input type="text" name="publication_attachments[{{ $attachment->id }}][title]" class="form-control input-sm" value="{{ old('publication_attachments.'.$attachment->id.'.title',$attachment->title) }}" placeholder="Judul tampilan (opsional)">
                <input type="hidden" name="publication_attachments[{{ $attachment->id }}][display_order]" value="{{ $attachment->display_order }}" data-attachment-order>
                <input type="hidden" name="attachment_sequence[]" value="existing:{{ $attachment->id }}" data-attachment-sequence>
                <label class="publication-attachment-remove"><input type="checkbox" name="remove_publication_attachments[]" value="{{ $attachment->id }}" data-attachment-remove> Hapus lampiran</label>
            </div>
            <div class="publication-attachment-controls">
                <button type="button" class="btn btn-default btn-xs" data-attachment-up aria-label="Naikkan urutan"><i class="fa fa-arrow-up"></i></button>
                <button type="button" class="btn btn-default btn-xs" data-attachment-down aria-label="Turunkan urutan"><i class="fa fa-arrow-down"></i></button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
</div><div class="box-footer {{ $resource==='galleries' ? 'gallery-form-actions' : '' }}"><a href="{{ route('admin.resources.index',$resource) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>@if($resource!=='categories')<button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>@endif<button type="submit" class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan</button></div></div>

</form>
@endsection
