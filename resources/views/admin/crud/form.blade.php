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
<textarea id="{{ $name }}" name="{{ $name }}" class="form-control" rows="5">{{ $value }}</textarea>
@elseif($type==='editor')
<x-admin.rich-editor :name="$name" :value="$value" />
@elseif($type==='image')
@php($selectedMedia=isset($field['relation']) ? data_get($item,$field['relation']) : null)
<x-admin.image-picker :name="$name" :label="$field['label']" :media="$media ?? collect()" :selected="$selectedMedia" :required="str_contains($field['rules']??'','required')" :show-label="false" :show-field-error="false" />
@else
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" class="form-control" @if(isset($field['step']))step="{{ $field['step'] }}"@endif>
@endif
@error($name)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
</div>
@endforeach
</div><div class="box-footer {{ $resource==='galleries' ? 'gallery-form-actions' : '' }}"><a href="{{ route('admin.resources.index',$resource) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>@if($resource!=='categories')<button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>@endif<button type="submit" class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan</button></div></div>

@if($resource==='galleries' && $item->exists)
<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> Foto Galeri</h3></div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group @error('gallery_item_upload') has-error @enderror">
                    <label class="control-label">Tambah Foto</label>
                    <input type="file" name="gallery_item_upload" class="form-control" accept="image/jpeg,image/png,image/webp">
                    @error('gallery_item_upload')<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group"><label class="control-label">Keterangan Foto</label><input type="text" name="gallery_item_caption" class="form-control" value="{{ old('gallery_item_caption') }}" maxlength="2000"></div>
            </div>
            <div class="col-md-2">
                <div class="form-group"><label class="control-label">Urutan</label><input type="number" name="gallery_item_order" class="form-control" value="{{ old('gallery_item_order',($galleryItems->max('display_order') ?? 0)+1) }}" min="0"></div>
            </div>
        </div>
        <p class="help-block">Pilih foto lalu klik Simpan untuk menambahkannya ke galeri ini.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead><tr><th style="width:90px">Foto</th><th>Keterangan</th><th style="width:90px">Urutan</th><th style="width:80px">Hapus</th></tr></thead>
                <tbody>
                @forelse($galleryItems as $galleryItem)
                    <tr>
                        <td>@if($galleryItem->media)<img src="{{ $galleryItem->media->thumbnail_url ?: $galleryItem->media->url }}" alt="{{ $galleryItem->media->alt_text }}" style="width:70px;height:50px;object-fit:cover">@else - @endif</td>
                        <td><input type="text" name="gallery_items[{{ $galleryItem->id }}][caption]" class="form-control" value="{{ old('gallery_items.'.$galleryItem->id.'.caption',$galleryItem->caption) }}" maxlength="2000"></td>
                        <td><input type="number" name="gallery_items[{{ $galleryItem->id }}][display_order]" class="form-control" value="{{ old('gallery_items.'.$galleryItem->id.'.display_order',$galleryItem->display_order) }}" min="0"></td>
                        <td class="text-center"><label title="Hapus foto saat disimpan"><input type="checkbox" name="remove_gallery_items[]" value="{{ $galleryItem->id }}"> <i class="fa fa-trash text-red"></i></label></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Belum ada foto dalam galeri ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
</form>
@endsection
