@props(['name'])
@php($icons=['dashboard'=>'▦','content'=>'▤','publication'=>'▧','profile'=>'⌂','official'=>'♙','gallery'=>'▣','media'=>'▨','data'=>'▥','map'=>'⌖','message'=>'✉','user'=>'♟','activity'=>'◴'])
<span {{ $attributes->class('admin-icon') }} aria-hidden="true">{{ $icons[$name] ?? '•' }}</span>
