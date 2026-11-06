{@--
  Komponen Flash Message — Notifikasi sukses/error dari server
  Digunakan di semua halaman untuk menampilkan pesan hasil aksi.
--@}
@php
    $flashTypes = [
        'success' => ['icon' => 'fas fa-check-circle', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
        'error'   => ['icon' => 'fas fa-times-circle', 'color' => 'text-rose-600',   'bg' => 'bg-rose-50',    'border' => 'border-rose-200'],
        'warning' => ['icon' => 'fas fa-exclamation-triangle', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
        'info'    => ['icon' => 'fas fa-info-circle', 'color' => 'text-blue-600',      'bg' => 'bg-blue-50',     'border' => 'border-blue-200'],
    ];
@endphp

@foreach(['success','error','warning','info'] as $type)
    @if(session($type))
        <div class="flash-alert flash-{{ $type }} {{ $flashTypes[$type]['bg'] }} {{ $flashTypes[$type]['border'] }} border-l-4 {{ $flashTypes[$type]['color'] }} rounded-xl p-4 mb-4 shadow-md flex items-center gap-3 animate-fade-in" role="alert">
            <div class="flex-shrink-0">
                <i class="{{ $flashTypes[$type]['icon'] }} text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-900">{{ session($type) }}</p>
            </div>
            <button type="button" class="inline-flex p-1.5 text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-slate-200 rounded-md" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">
                <span class="sr-only">Tutup</span>
                <i class="fas fa-times"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const el = document.querySelector('.flash-{{ $type }}');
                if (el) { el.style.opacity = '0'; el.style.transform = 'translateY(-10px)'; setTimeout(() => el.remove(), 300); }
            }, 5000);
        </script>
    @endif
@endforeach

@push('styles')
<style>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: fade-in 0.35s ease forwards; }
</style>
@endpush