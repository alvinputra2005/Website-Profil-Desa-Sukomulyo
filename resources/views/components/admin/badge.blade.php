@props(['value'])
@php
    $class=match((string)$value){'published','1'=>'label-success','draft'=>'label-default','archived','0'=>'label-danger',default=>'label-info'};
    $text=match((string)$value){'published'=>'Terbit','draft'=>'Draf','archived'=>'Arsip','1'=>'Ya','0'=>'Tidak',default=>$value};
    $isPublicationStatus=in_array((string)$value,['published','draft','archived'],true);
@endphp
<span {{ $attributes->class("label $class".($isPublicationStatus?' admin-status-badge':'')) }}>{{ $text }}</span>
