@props(['name','label','value'=>'','type'=>'text','required'=>false])
@php($current=old($name,$value))
<div class="form-group {{ $errors->has($name)?'has-error':'' }}">
<label for="{{ $name }}" class="control-label {{ $required?'required':'' }}">{{ $label }}</label>
@if($type==='textarea')
<textarea id="{{ $name }}" name="{{ $name }}" class="form-control" rows="4">{{ $current }}</textarea>
@elseif($type==='editor')
<x-admin.rich-editor :name="$name" :value="$current" />
@else
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}" class="form-control">
@endif
@error($name)<span class="field-error"><i class="fa fa-times-circle-o"></i> {{ $message }}</span>@enderror
</div>
