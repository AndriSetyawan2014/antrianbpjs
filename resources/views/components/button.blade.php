{@--
  Komponen Tombol Standar — Sistem Pemantauan Bridging BPJS
  Params:
    - type: button|submit|reset (default: button)
    - variant: primary|secondary|success|warning|danger|outline|ghost (default: primary)
    - size: sm|md|lg (default: md)
    - loading: boolean (default: false) — tampilkan spinner saat diproses
    - disabled: boolean (default: false)
    - class: string — kelas CSS tambahan
    - icon: string — FontAwesome icon class (opsional)
    - iconPosition: left|right (default: left)
    - title: string — teks tombol
    - href: string — jika diisi, render sebagai <a>
    - data-* : atribut tambahan via $attributes
--@}
@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'class' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'title' => null,
    'href' => null,
])

<?php
$variantClasses = [
    'primary'   => 'bg-#0f172a text-white hover:bg-#1e293b border-#0f172a',
    'secondary' => 'bg-#475569 text-white hover:bg-#334155 border-#475569',
    'success'   => 'bg-#059669 text-white hover:bg-#047857 border-#059669',
    'warning'   => 'bg-#d97706 text-white hover:bg-#b45309 border-#d97706',
    'danger'    => 'bg-#dc2626 text-white hover:bg-#b91c1c border-#dc2626',
    'outline'   => 'bg-transparent text-#0f172a border-#0f172a hover:bg-#0f172a hover:text-white',
    'ghost'     => 'bg-transparent text-#475569 hover:bg-#f1f5f9 border-transparent',
];

$sizeClasses = [
    'sm' => 'px-2.5 py-1.5 text-xs',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$btnClass = 'btn btn-' . $variant . ' btn-' . $size . ' inline-flex items-center justify-center gap-2 rounded-md font-medium transition-all duration-200 border focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($class ?? '');
$disabledAttr = $disabled || $loading ? 'disabled' : '';
$typeAttr = $href ? null : ('type="' . $type . '"');

if ($href):
?>
<a href="{{ $href }}" {{ $attributes->merge(['class' => $btnClass, 'disabled' => $disabledAttr]) }}>
    @if($icon && $iconPosition === 'left')<i class="{{ $icon }}"></i>@endif
    {{ $title }}
    @if($icon && $iconPosition === 'right')<i class="{{ $icon }}"></i>@endif
    @if($loading)<span class="loading-dots"></span>@endif
</a>
@else
<button {{ $typeAttr }} {{ $attributes->merge(['class' => $btnClass, 'disabled' => $disabledAttr]) }}>
    @if($icon && $iconPosition === 'left')<i class="{{ $icon }}"></i>@endif
    {{ $title }}
    @if($icon && $iconPosition === 'right')<i class="{{ $icon }}"></i>@endif
    @if($loading)
        <span class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
    @endif
</button>
@endif

@push('styles')
<style>
.btn-primary,
.btn-secondary,
.btn-success,
.btn-warning,
.btn-danger,
.btn-outline,
.btn-ghost {
  @apply inline-flex items-center justify-center gap-2 rounded-md font-medium transition-all duration-200 border focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none;
}

.btn-sm { @apply px-2.5 py-1.5 text-xs; }
.btn-md { @apply px-4 py-2 text-sm; }
.btn-lg { @apply px-6 py-3 text-base; }

.btn-primary {
  @apply bg-slate-900 text-white border-slate-900 hover:bg-slate-800;
}
.btn-secondary {
  @apply bg-slate-500 text-white border-slate-500 hover:bg-slate-600;
}
.btn-success {
  @apply bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700;
}
.btn-warning {
  @apply bg-amber-600 text-white border-amber-600 hover:bg-amber-700;
}
.btn-danger {
  @apply bg-rose-600 text-white border-rose-600 hover:bg-rose-700;
}
.btn-outline {
  @apply bg-transparent text-slate-900 border-slate-900 hover:bg-slate-900 hover:text-white;
}
.btn-ghost {
  @apply bg-transparent text-slate-600 hover:bg-slate-100 border-transparent;
}

.loading-dots {
  display: inline-block;
  position: relative;
  width: 60px;
  height: 20px;
}
.loading-dots::after {
  content: '...';
  animation: dots 1.5s steps(4, end) infinite;
}
@keyframes dots {
  0%, 20% { color: rgba(0,0,0,0); text-shadow: .25em 0 0 rgba(0,0,0,0), .5em 0 0 rgba(0,0,0,0); }
  40% { color: rgba(0,0,0,1); text-shadow: .25em 0 0 rgba(0,0,0,0), .5em 0 0 rgba(0,0,0,0); }
  60% { text-shadow: .25em 0 0 rgba(0,0,0,1), .5em 0 0 rgba(0,0,0,0); }
  80%, 100% { text-shadow: .25em 0 0 rgba(0,0,0,1), .5em 0 0 rgba(0,0,0,1); }
}
</style>
@endpushOnce
