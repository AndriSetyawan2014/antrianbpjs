{{-- ═══════════════════════════════════════════
     SIDENAV — Sistem Pemantauan Bridging BPJS
     resources/views/layouts/sidenav.blade.php
     ═══════════════════════════════════════════ --}}

<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <a href="{{ url('/dashboard') }}" class="sidebar-logo">
        <img src="{{ asset('dist/img/logoqlheader.png') }}" alt="Queen Latifa Logo">
    </a>

    <!-- Pencarian menu (filter cepat) -->
    <div class="sidebar-search">
        <i class="fas fa-search" aria-hidden="true"></i>
        <input type="search" id="sidebarSearch" placeholder="Cari menu…" aria-label="Cari menu navigasi" autocomplete="off">
    </div>
    <div class="sidebar-search-empty" id="sidebarSearchEmpty" style="display:none;">
        <i class="fas fa-search-minus me-1"></i> Menu tidak ditemukan
    </div>

    <nav id="sidebarNav">
        <!-- Home -->
        <a href="{{ url('/dashboard') }}"
           class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home nav-icon"></i>
            <span>Dashboard</span>
        </a>

        {{-- ══ YOGYAKARTA ══ --}}
        <div class="sidebar-section-label">
            <i class="fas fa-map-marker-alt"></i> Queen Latifa Yogyakarta
        </div>

        {{-- Kode Booking Yogyakarta --}}
        <button type="button" class="nav-link sidebar-collapse-btn" data-bs-toggle="collapse"
           data-bs-target="#ddKodeBookingJogja"
           aria-expanded="{{ Request::is('data_kodebooking*') || Request::is('rekap_kodebooking*') ? 'true' : 'false' }}">
            <i class="fas fa-calendar-check nav-icon"></i>
            <span>Kode Booking</span>
            <i class="fas fa-angle-down arrow-icon {{ Request::is('data_kodebooking*') || Request::is('rekap_kodebooking*') ? 'rotated' : '' }}"></i>
        </button>
        <div class="collapse {{ Request::is('data_kodebooking*') || Request::is('rekap_kodebooking*') ? 'show' : '' }}"
             id="ddKodeBookingJogja">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="{{ url('/data_kodebooking') }}"
                       class="nav-link {{ Request::is('data_kodebooking') ? 'active' : '' }}">
                        <i class="fas fa-table nav-icon"></i> Data Kode Booking
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/rekap_kodebooking') }}"
                       class="nav-link {{ Request::is('rekap_kodebooking') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar nav-icon"></i> Rekap Kode Booking
                    </a>
                </li>
            </ul>
        </div>

        {{-- Task ID Yogyakarta --}}
        <button type="button" class="nav-link sidebar-collapse-btn" data-bs-toggle="collapse"
           data-bs-target="#ddTaskIDJogja"
           aria-expanded="{{ Request::is('TaskID*') || Request::is('rekap_taskid*') ? 'true' : 'false' }}">
            <i class="fas fa-tasks nav-icon"></i>
            <span>Task ID</span>
            <i class="fas fa-angle-down arrow-icon {{ Request::is('TaskID*') || Request::is('rekap_taskid*') ? 'rotated' : '' }}"></i>
        </button>
        <div class="collapse {{ Request::is('TaskID*') || Request::is('rekap_taskid*') ? 'show' : '' }}"
             id="ddTaskIDJogja">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="{{ url('/TaskID') }}"
                       class="nav-link {{ Request::is('TaskID') ? 'active' : '' }}">
                        <i class="fas fa-file-medical nav-icon"></i> Data Task ID
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/rekap_taskid') }}"
                       class="nav-link {{ Request::is('rekap_taskid') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list nav-icon"></i> Rekap Task ID
                    </a>
                </li>
            </ul>
        </div>

        {{-- ══ KULON PROGO ══ --}}
        <div class="sidebar-section-label">
            <i class="fas fa-map-marker-alt"></i> Queen Latifa Kulon Progo
        </div>

        {{-- Kode Booking Kulon Progo --}}
        <button type="button" class="nav-link sidebar-collapse-btn" data-bs-toggle="collapse"
           data-bs-target="#ddKodeBookingKP"
           aria-expanded="{{ Request::is('qlkp_data_kodebooking*') || Request::is('qlkp_rekap_kodebooking*') ? 'true' : 'false' }}">
            <i class="fas fa-calendar-check nav-icon"></i>
            <span>Kode Booking</span>
            <i class="fas fa-angle-down arrow-icon {{ Request::is('qlkp_data_kodebooking*') || Request::is('qlkp_rekap_kodebooking*') ? 'rotated' : '' }}"></i>
        </button>
        <div class="collapse {{ Request::is('qlkp_data_kodebooking*') || Request::is('qlkp_rekap_kodebooking*') ? 'show' : '' }}"
             id="ddKodeBookingKP">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="{{ url('/qlkp_data_kodebooking') }}"
                       class="nav-link {{ Request::is('qlkp_data_kodebooking') ? 'active' : '' }}">
                        <i class="fas fa-table nav-icon"></i> Data Kode Booking
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/qlkp_rekap_kodebooking') }}"
                       class="nav-link {{ Request::is('qlkp_rekap_kodebooking') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar nav-icon"></i> Rekap Kode Booking
                    </a>
                </li>
            </ul>
        </div>

        {{-- Task ID Kulon Progo --}}
        <button type="button" class="nav-link sidebar-collapse-btn" data-bs-toggle="collapse"
           data-bs-target="#ddTaskIDKP"
           aria-expanded="{{ Request::is('qlkp_TaskID*') || Request::is('qlkp_rekap_taskid*') ? 'true' : 'false' }}">
            <i class="fas fa-tasks nav-icon"></i>
            <span>Task ID</span>
            <i class="fas fa-angle-down arrow-icon {{ Request::is('qlkp_TaskID*') || Request::is('qlkp_rekap_taskid*') ? 'rotated' : '' }}"></i>
        </button>
        <div class="collapse {{ Request::is('qlkp_TaskID*') || Request::is('qlkp_rekap_taskid*') ? 'show' : '' }}"
             id="ddTaskIDKP">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="{{ url('/qlkp_TaskID') }}"
                       class="nav-link {{ Request::is('qlkp_TaskID') ? 'active' : '' }}">
                        <i class="fas fa-file-medical nav-icon"></i> Data Task ID
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/qlkp_rekap_taskid') }}"
                       class="nav-link {{ Request::is('qlkp_rekap_taskid') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list nav-icon"></i> Rekap Task ID
                    </a>
                </li>
            </ul>
        </div>

        {{-- ══ TEMANGGUNG ══ --}}
        <div class="sidebar-section-label">
            <i class="fas fa-map-marker-alt"></i> Queen Latifa Temanggung
        </div>

        {{-- Kode Booking Temanggung --}}
        <button type="button" class="nav-link sidebar-collapse-btn" data-bs-toggle="collapse"
           data-bs-target="#ddKodeBookingTMG"
           aria-expanded="{{ Request::is('qltmg_data_kodebooking*') || Request::is('qltmg_rekap_kodebooking*') ? 'true' : 'false' }}">
            <i class="fas fa-calendar-check nav-icon"></i>
            <span>Kode Booking</span>
            <i class="fas fa-angle-down arrow-icon {{ Request::is('qltmg_data_kodebooking*') || Request::is('qltmg_rekap_kodebooking*') ? 'rotated' : '' }}"></i>
        </button>
        <div class="collapse {{ Request::is('qltmg_data_kodebooking*') || Request::is('qltmg_rekap_kodebooking*') ? 'show' : '' }}"
             id="ddKodeBookingTMG">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="{{ url('/qltmg_data_kodebooking') }}"
                       class="nav-link {{ Request::is('qltmg_data_kodebooking') ? 'active' : '' }}">
                        <i class="fas fa-table nav-icon"></i> Data Kode Booking
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/qltmg_rekap_kodebooking') }}"
                       class="nav-link {{ Request::is('qltmg_rekap_kodebooking') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar nav-icon"></i> Rekap Kode Booking
                    </a>
                </li>
            </ul>
        </div>

        {{-- Task ID Temanggung --}}
        <button type="button" class="nav-link sidebar-collapse-btn" data-bs-toggle="collapse"
           data-bs-target="#ddTaskIDTMG"
           aria-expanded="{{ Request::is('qltmg_TaskID*') || Request::is('qltmg_rekap_taskid*') ? 'true' : 'false' }}">
            <i class="fas fa-tasks nav-icon"></i>
            <span>Task ID</span>
            <i class="fas fa-angle-down arrow-icon {{ Request::is('qltmg_TaskID*') || Request::is('qltmg_rekap_taskid*') ? 'rotated' : '' }}"></i>
        </button>
        <div class="collapse {{ Request::is('qltmg_TaskID*') || Request::is('qltmg_rekap_taskid*') ? 'show' : '' }}"
             id="ddTaskIDTMG">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="{{ url('/qltmg_TaskID') }}"
                       class="nav-link {{ Request::is('qltmg_TaskID') ? 'active' : '' }}">
                        <i class="fas fa-file-medical nav-icon"></i> Data Task ID
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/qltmg_rekap_taskid') }}"
                       class="nav-link {{ Request::is('qltmg_rekap_taskid') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list nav-icon"></i> Rekap Task ID
                    </a>
                </li>
            </ul>
        </div>

        {{-- ══ VCLAIM ══ --}}
        <div class="sidebar-section-label" style="color:#86efac;">
            <i class="fas fa-stethoscope"></i> VClaim Monitoring
        </div>

        {{-- Monitoring Rawat Jalan --}}
        <button type="button" class="nav-link sidebar-collapse-btn" data-bs-toggle="collapse"
           data-bs-target="#ddMonitoringRawatJalan"
           aria-expanded="{{ Request::is('vclaim/kunjungan-rawat-jalan*') || Request::is('vclaim/rekap-kunjungan-rawat-jalan*') ? 'true' : 'false' }}">
            <i class="fas fa-walking nav-icon" style="color:#86efac;"></i>
            <span>Monitoring Rawat Jalan</span>
            <i class="fas fa-angle-down arrow-icon {{ Request::is('vclaim/kunjungan-rawat-jalan*') || Request::is('vclaim/rekap-kunjungan-rawat-jalan*') ? 'rotated' : '' }}"></i>
        </button>
        <div class="collapse {{ Request::is('vclaim/kunjungan-rawat-jalan*') || Request::is('vclaim/rekap-kunjungan-rawat-jalan*') ? 'show' : '' }}"
             id="ddMonitoringRawatJalan">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="{{ route('vclaim.kunjungan.jalan') }}"
                       class="nav-link {{ Request::is('vclaim/kunjungan-rawat-jalan') ? 'active' : '' }}">
                        <i class="fas fa-list nav-icon" style="color:#86efac;"></i> Detail
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('vclaim.rekap.kunjungan.jalan') }}"
                       class="nav-link {{ Request::is('vclaim/rekap-kunjungan-rawat-jalan') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar nav-icon" style="color:#86efac;"></i> Rekap
                    </a>
                </li>
            </ul>
        </div>

        {{-- Endpoint Status --}}
        <a href="{{ route('vclaim.endpoint.status') }}"
           class="nav-link {{ Request::is('vclaim/endpoint-status*') ? 'active' : '' }}">
            <i class="fas fa-network-wired nav-icon" style="color:#86efac;"></i>
            <span>Endpoint Status</span>
        </a>

        {{-- Pengaturan Sistem --}}
        <div class="sidebar-section-label" style="color:#fcd34d; margin-top: 20px;">
            <i class="fas fa-cogs"></i> Sistem
        </div>
        <a href="{{ route('settings.index') }}"
           class="nav-link {{ Request::is('settings*') ? 'active' : '' }}">
            <i class="fas fa-tools nav-icon" style="color:#fcd34d;"></i>
            <span>Pengaturan</span>
        </a>

    </nav>
</div>

<style>
/* Arrow rotation for open collapse */
.arrow-icon.rotated,
[aria-expanded="true"] .arrow-icon {
    transform: rotate(180deg);
}
</style>