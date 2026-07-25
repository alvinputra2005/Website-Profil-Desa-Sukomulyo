@props(['name'])
@php($icons=['dashboard'=>'▦','content'=>'▤','publication'=>'▧','profile'=>'⌂','official'=>'♙','gallery'=>'▣','media'=>'▨','data'=>'▥','map'=>'⌖','message'=>'✉','user'=>'♟','settings'=>'⚙','activity'=>'◴','redirect'=>'↪'])
<span {{ $attributes->class('admin-icon') }} aria-hidden="true">{{ $icons[$name] ?? '•' }}</span>
