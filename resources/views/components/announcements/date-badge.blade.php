@props(['date'])

@php
    $months = [1 => 'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
@endphp

<time class="announcement-date" datetime="{{ $date->toDateString() }}">
    <span class="announcement-date-day">{{ $date->format('d') }}</span>
    <span class="announcement-date-month">{{ $months[$date->month] }}</span>
    <span class="announcement-date-year">{{ $date->format('Y') }}</span>
</time>
