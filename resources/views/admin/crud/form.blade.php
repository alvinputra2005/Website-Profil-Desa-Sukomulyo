@extends('layouts.admin')
@section('title',($item->exists?'Ubah ':'Tambah ').$config['title'])
@section('page-description','Lengkapi formulir berikut')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $item->exists?route('admin.resources.update',[$resource,$item]):route('admin.resources.store',$resource) }}" data-dirty-form>
@csrf @if($item->exists)@method('put')@endif
<div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Form {{ $config['title'] }}</h3></div><div class="box-body">
@foreach($config['fields'] as $name=>$field)
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
@endforeach
</div><div class="box-footer {{ $resource==='galleries' ? 'gallery-form-actions' : '' }}"><a href="{{ route('admin.resources.index',$resource) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>@if($resource!=='categories')<button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>@endif<button type="submit" class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan</button></div></div>

</form>
@endsection
