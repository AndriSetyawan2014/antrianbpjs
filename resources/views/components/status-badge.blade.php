{{-- Komponen badge status BPJS standar: ✓ 200 OK / ⏳ Pending / ✗ Err CODE --}}
@props(['code'])
@php
    $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
    $badgeLabel = $code == 200 ? '✓ 200 OK' : ($code == 0 ? '⏳ Pending' : '✗ Err '.$code);
@endphp
<span {{ $attributes->merge(['class' => 'status-badge '.$badgeClass]) }} title="HTTP {{ $code }}">{{ $badgeLabel }}</span>
