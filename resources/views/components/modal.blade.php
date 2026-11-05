{@--
  Komponen Modal Standar — Sistem Pemantauan Bridging BPJS
  Params:
    - id: string (wajib) — ID modal
    - title: string — Judul modal
    - size: sm|md|lg|xl|full (default: md)
    - headerClass: string — Kelas CSS tambahan header
    - bodyClass: string — Kelas CSS tambahan body
    - footerClass: string — Kelas CSS tambahan footer
    - showClose: boolean (default: true)
    - slot: isi body modal
    - footerSlot: isi footer modal (opsional)
--@}
@props([
    'id' => null,
    'title' => null,
    'size' => 'md',
    'headerClass' => null,
    'bodyClass' => null,
    'footerClass' => null,
    'showClose' => true,
])

<?php
$sizeMap = [
    'sm'   => 'modal-sm',
    'md'   => 'modal-md',
    'lg'   => 'modal-lg',
    'xl'   => 'modal-xl',
    'full' => 'modal-fullscreen',
];
$modalSize = $sizeMap[$size] ?? 'modal-md';
?>

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" @if($title) aria-labelledby="{{ $id }}Label" @endif>
    <div class="modal-dialog {{ $modalSize }} modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            {{-- Header --}}
            <div class="modal-header {{ $headerClass ?? 'bg-' . ($title ? 'primary' : 'surface') . ' text-white' }}">
                @if($title)
                    <h5 class="modal-title" id="{{ $id }}Label">
                        <i class="fas fa-info-circle me-2 opacity-75"></i>{{ $title }}
                    </h5>
                @endif
                @if($showClose)
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                @endif
            </div>

            {{-- Body --}}
            <div class="modal-body {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if(isset($footerSlot))
                <div class="modal-footer {{ $footerClass ?? 'bg-surface-alt' }}">
                    {{ $footerSlot }}
                </div>
            @endif
        </div>
    </div>
</div>

@pushOnce('styles')
<style>
.modal-dialog-centered { display: flex; align-items: center; min-height: calc(100% - 2rem); }
.modal-content { border-radius: var(--radius-lg); border: none; }
.modal-header { border-bottom: none; padding: 1rem 1.25rem; }
.modal-body { padding: 1.25rem; }
.modal-footer { border-top: none; padding: 1rem 1.25rem; }
.modal-fullscreen .modal-content { height: 100vh; max-height: 100vh; }
</style>
@endpushOnce