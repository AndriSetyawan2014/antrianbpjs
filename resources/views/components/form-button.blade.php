{{--
  Komponen Tombol Form — Submit/Reset/Button dengan loading state
  Params:
    - type: submit|reset|button (default: submit)
    - variant: primary|secondary|success|warning|danger|outline|ghost (default: primary)
    - size: sm|md|lg (default: md)
    - loading: boolean (default: false)
    - disabled: boolean (default: false)
    - icon: string — FontAwesome icon (opsional)
    - iconPosition: left|right (default: left)
    - title: string — Teks tombol
    - class: string — Kelas CSS tambahan
--}}
@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'title' => null,
    'class' => null,
])

<?php
$btnClass = 'btn btn-' . $variant . ' btn-' . $size . ' inline-flex items-center justify-center gap-2 rounded-md font-medium transition-all duration-200 border focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

$variantClasses = [
    'primary'   => 'bg-slate-900 text-white border-slate-900 hover:bg-slate-800',
    'secondary' => 'bg-slate-500 text-white border-slate-500 hover:bg-slate-600',
    'success'   => 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700',
    'warning'   => 'bg-amber-600 text-white border-amber-600 hover:bg-amber-700',
    'danger'    => 'bg-rose-600 text-white border-rose-600 hover:bg-rose-700',
    'outline'   => 'bg-transparent text-slate-900 border-slate-900 hover:bg-slate-900 hover:text-white',
    'ghost'     => 'bg-transparent text-slate-600 hover:bg-slate-100 border-transparent',
];
$sizeClasses = [
    'sm' => 'px-2.5 py-1.5 text-xs',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$btnClass .= ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($class ?? '');
$disabledAttr = $disabled || $loading ? 'disabled' : '';
?>

<button {{ $attributes->merge(['type' => $type, 'class' => $btnClass, 'disabled' => $disabledAttr]) }}>
    @if($icon && $iconPosition === 'left')<i class="{{ $icon }}"></i>@endif
    {{ $title }}
    @if($icon && $iconPosition === 'right')<i class="{{ $icon }}"></i>@endif
    @if($loading)
        <span class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
    @endif
</button>
