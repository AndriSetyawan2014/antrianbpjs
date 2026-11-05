{@--
  Komponen Skeleton Loading — Sistem Pemantauan Bridging BPJS
  Digunakan saat menunggu data dari server agar halaman tidak terlihat kosong.
  
  Params:
    - type: text|avatar|button|table-row|stat-card|chart (default: text)
    - count: jumlah elemen skeleton (default: 1)
    - class: kelas CSS tambahan
    - width: lebar custom (opsional)
    - height: tinggi custom (opsional)
--@}
@props([
    'type' => 'text',
    'count' => 1,
    'class' => null,
    'width' => null,
    'height' => null,
])

<?php
$style = '';
if ($width)  $style .= "width:{$width};";
if ($height) $style .= "height:{$height};";
?>

@php
    $animClass = 'animate-pulse';
    $baseClass = 'skeleton-base';
@endphp

<div class="skeleton-container {{ $class ?? '' }}" style="{{ $style }}">
    @for($i = 0; $i < $count; $i++)
        <div class="skeleton-{{ $type }} {{ $animClass }} {{ $baseClass }}"></div>
    @endfor
</div>

@push('styles')
<style>
/* ── Skeleton Base ── */
.skeleton-base {
  background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%);
  background-size: 200% 100%;
  animation: skeleton-pulse 1.5s ease-in-out infinite;
  border-radius: var(--radius-sm);
  border: none;
}

@keyframes skeleton-pulse {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Skeleton Container ── */
.skeleton-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* ── Variants ── */
.skeleton-text { height: 1rem; }
.skeleton-text.sm { height: 0.875rem; }
.skeleton-text.md { height: 1rem; }
.skeleton-text.lg { height: 1.25rem; }

.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
}

.skeleton-button {
  width: 120px;
  height: 36px;
  border-radius: var(--radius-sm);
}

.skeleton-table-row {
  height: 48px;
  width: 100%;
  border-bottom: 1px solid var(--color-border);
  margin-bottom: 0.5rem;
}

.skeleton-stat-card {
  height: 120px;
  border-radius: var(--radius-md);
}

.skeleton-chart {
  height: 260px;
  border-radius: var(--radius-md);
}

/* ── Table Row Skeleton ── */
.skeleton-table-row::before {
  content: "";
  display: block;
  height: 0.75rem;
  width: 60%;
  background: inherit;
  border-radius: 4px;
  margin: 0.5rem 0;
}

.skeleton-table-row::after {
  content: "";
  display: block;
  height: 0.75rem;
  width: 35%;
  background: inherit;
  border-radius: 4px;
}

/* ── Stat Card Skeleton ── */
.skeleton-stat-card::before {
  content: "";
  display: block;
  height: 2rem;
  width: 70%;
  background: inherit;
  border-radius: 4px;
  margin: 1.5rem 0 1rem;
}

.skeleton-stat-card::after {
  content: "";
  display: block;
  height: 0.75rem;
  width: 40%;
  background: inherit;
  border-radius: 4px;
}

/* ── Loading Overlay ── */
.loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  border-radius: inherit;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
@endpushOnce
