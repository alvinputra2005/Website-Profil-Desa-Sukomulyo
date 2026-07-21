@extends('layouts.admin')
@section('title',($item->exists?'Ubah ':'Tambah ').$config['title'])
@section('page-description','Lengkapi formulir berikut')
@section('content')
<form method="post" action="{{ $item->exists?route('admin.resources.update',[$resource,$item]):route('admin.resources.store',$resource) }}" class="form-horizontal" data-dirty-form>
@csrf @if($item->exists)@method('put')@endif
<div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Form {{ $config['title'] }}</h3></div><div class="box-body">
@foreach($config['fields'] as $name=>$field)
@php
    $type=$field['type']??'text';
    $value=old($name,data_get($item,$name));
    if($value instanceof \Carbon\CarbonInterface) $value=$type==='date'?$value->format('Y-m-d'):$value->format('Y-m-d\TH:i');
    if(is_array($value)) $value=json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
@endphp
<div class="form-group {{ $errors->has($name)?'has-error':'' }}"><label for="{{ $name }}" class="col-sm-3 control-label {{ str_contains($field['rules']??'','required')?'required':'' }}">{{ $field['label'] }}</label><div class="col-sm-8">
@if($type==='select')
<select id="{{ $name }}" name="{{ $name }}" class="form-control select2">@foreach($field['options'] as $option=>$label)<option value="{{ $option }}" @selected((string)$value===(string)$option)>{{ $label }}</option>@endforeach</select>
@elseif($type==='relation')
<select id="{{ $name }}" name="{{ $name }}" class="form-control select2"><option value="">-- Pilih --</option>@foreach($relations[$name] as $relation)<option value="{{ $relation->id }}" @selected((string)$value===(string)$relation->id)>{{ $relation->{$field['display']} }}</option>@endforeach</select>
@elseif($type==='toggle')
<label class="checkbox-inline"><input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked((bool)$value)> Ya / Aktif</label>
@elseif($type==='textarea')
<textarea id="{{ $name }}" name="{{ $name }}" class="form-control" rows="5">{{ $value }}</textarea>
@elseif($type==='editor')
<div data-editor><div class="btn-group editor-toolbar"><button type="button" class="btn btn-default btn-sm" data-command="bold"><i class="fa fa-bold"></i></button><button type="button" class="btn btn-default btn-sm" data-command="italic"><i class="fa fa-italic"></i></button><button type="button" class="btn btn-default btn-sm" data-command="insertUnorderedList"><i class="fa fa-list-ul"></i></button></div><div class="editor-surface" contenteditable="true"></div><textarea hidden id="{{ $name }}" name="{{ $name }}">{{ $value }}</textarea></div>
@else
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" class="form-control" @if(isset($field['step']))step="{{ $field['step'] }}"@endif>
@endif
@error($name)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
</div></div>
@endforeach
</div><div class="box-footer"><a href="{{ route('admin.resources.index',$resource) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a><button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button><button type="submit" class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan</button></div></div></form>
@endsection
