<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        height: 100vh;
        width: 260px;
        background: #0C356A;
        padding-top: 10px;
        padding-left: 10px;
        overflow-y: auto;
        transition: width 0.3s ease;
        z-index: 5;
    }

    .sidebar.hidden {
        transform: translateX(-100%);
    }

    .sidebar .nav {
        padding-bottom: 10px;
    }

    .sidebar a {
        padding: 8px 15px;
        text-decoration: none;
        font-size: 14px;
        color: #ecf0f1;
        display: block;
        margin-bottom: 5px;
    }

    .sidebar a:hover {
        background-color: #ffc436;
        color: white;
    }

    .sidebar i {
        font-size: 15px;
    }

    .nav-link.active {
        background-color: #ffc436;
        color: white;
        font-weight: bold;
    }

    /* Button to toggle sidebar */
    .toggle-sidebar {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 10;
        background-color: #0C356A;
        color: white;
        border: none;
        padding: 10px;
        cursor: pointer;
    }

    @media (min-width: 768px) {
        .toggle-sidebar {
            display: none; /* Hide toggle button on larger screens */
        }
    }
</style>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4" id="sidebar"></aside>
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav>
                <div>
                    <a class="{{ url('/dashboard') }}" class="nav-link">
                        <img src="{{ asset('dist/img/logoqlheader.png') }}" alt="AdminLTE Logo" style="height: 38px;">
                    </a>
                </div>

                <a href="{{ url('/dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span class="d-none d-md-inline">Home</span>
                </a>
                <!-- Yogyakarta -->
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#dropdownMenuJogja">
                    <i class="fas fa-map-marker-alt nav-icon"></i> Queen Latifa Yogyakarta
                    <i class="fas fa-angle-right ms-auto arrow-jogja"></i>
                </a>
                <div class="collapse {{ Request::is('data_kodebooking*') || Request::is('rekap_kodebooking*') || Request::is('TaskID*') || Request::is('rekap_taskid*') ? 'show' : '' }}"
                    id="dropdownMenuJogja">
                    <ul class="nav flex-column ms-4">
                        <!-- Kode Booking -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse"
                                data-bs-target="#dropdownMenuKodeBookingJogja">
                                <i class="fas fa-cogs nav-icon"></i> Kode Booking
                                <i class="fas fa-angle-right ms-auto arrow-kodebooking-jogja"></i>
                            </a>
                            <div class="collapse {{ Request::is('data_kodebooking*') || Request::is('rekap_kodebooking*') ? 'show' : '' }}"
                                id="dropdownMenuKodeBookingJogja">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/data_kodebooking') }}"
                                            class="nav-link {{ Request::is('data_kodebooking') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Data Kode Booking
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/rekap_kodebooking') }}"
                                            class="nav-link {{ Request::is('rekap_kodebooking') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Rekap Data Kode Booking
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- Task ID -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse"
                                data-bs-target="#dropdownMenuTaskIDJogja">
                                <i class="fas fa-tasks nav-icon"></i> Task ID
                                <i class="fas fa-angle-right ms-auto arrow-taskid-jogja"></i>
                            </a>
                            <div class="collapse {{ Request::is('TaskID*') || Request::is('rekap_taskid*') ? 'show' : '' }}"
                                id="dropdownMenuTaskIDJogja">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/TaskID') }}"
                                            class="nav-link {{ Request::is('TaskID') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Data Task ID
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/rekap_taskid') }}"
                                            class="nav-link {{ Request::is('rekap_taskid') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Rekap Data Task ID
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Kulon Progo -->
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#dropdownMenuKulonProgo">
                    <i class="fas fa-map-marker-alt nav-icon"></i> Queen Latifa Kulon Progo
                    <i class="fas fa-angle-right ms-auto arrow-kulonprogo"></i>
                </a>
                <div class="collapse {{ Request::is('qlkp_data_kodebooking*') || Request::is('qlkp_rekap_kodebooking*') || Request::is('qlkp_TaskID*') || Request::is('qlkp_rekap_taskid*') ? 'show' : '' }}"
                    id="dropdownMenuKulonProgo">
                    <ul class="nav flex-column ms-4">
                        <!-- Kode Booking -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse"
                                data-bs-target="#dropdownMenuKodeBookingKulonProgo">
                                <i class="fas fa-cogs nav-icon"></i> Kode Booking
                                <i class="fas fa-angle-right ms-auto arrow-kodebooking-kulonprogo"></i>
                            </a>
                            <div class="collapse {{ Request::is('qlkp_data_kodebooking*') || Request::is('qlkp_rekap_kodebooking*') ? 'show' : '' }}"
                                id="dropdownMenuKodeBookingKulonProgo">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_data_kodebooking') }}"
                                            class="nav-link {{ Request::is('qlkp_data_kodebooking') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Data Kode Booking
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_rekap_kodebooking') }}"
                                            class="nav-link {{ Request::is('qlkp_rekap_kodebooking') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Rekap Data Kode Booking
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- Task ID -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse"
                                data-bs-target="#dropdownMenuTaskIDKulonProgo">
                                <i class="fas fa-tasks nav-icon"></i> Task ID
                                <i class="fas fa-angle-right ms-auto arrow-taskid-kulonprogo"></i>
                            </a>
                            <div class="collapse {{ Request::is('qlkp_TaskID*') || Request::is('qlkp_rekap_taskid*') ? 'show' : '' }}"
                                id="dropdownMenuTaskIDKulonProgo">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_TaskID') }}"
                                            class="nav-link {{ Request::is('qlkp_TaskID') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Data Task ID
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_rekap_taskid') }}"
                                            class="nav-link {{ Request::is('qlkp_rekap_taskid') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Rekap Data Task ID
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Temanggung -->
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#dropdownMenuTemanggung">
                    <i class="fas fa-map-marker-alt nav-icon"></i> Queen Latifa Temanggung
                    <i class="fas fa-angle-right ms-auto arrow-temanggung"></i>
                </a>
                <div class="collapse {{ Request::is('qltmg_data_kodebooking*') || Request::is('qltmg_rekap_kodebooking*') || Request::is('qltmg_TaskID*') || Request::is('qltmg_rekap_taskid*') ? 'show' : '' }}"
                    id="dropdownMenuTemanggung">
                    <ul class="nav flex-column ms-4">
                        <!-- Kode Booking -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse"
                                data-bs-target="#dropdownMenuKodeBookingTemanggung">
                                <i class="fas fa-cogs nav-icon"></i> Kode Booking
                                <i class="fas fa-angle-right ms-auto arrow-kodebooking-temanggung"></i>
                            </a>
                            <div class="collapse {{ Request::is('qltmg_data_kodebooking*') || Request::is('qltmg_rekap_kodebooking*') ? 'show' : '' }}"
                                id="dropdownMenuKodeBookingTemanggung">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/qltmg_data_kodebooking') }}"
                                            class="nav-link {{ Request::is('qltmg_data_kodebooking') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Data Kode Booking
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/qltmg_rekap_kodebooking') }}"
                                            class="nav-link {{ Request::is('qltmg_rekap_kodebooking') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Rekap Data Kode Booking
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- Task ID -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse"
                                data-bs-target="#dropdownMenuTaskIDTemanggung">
                                <i class="fas fa-tasks nav-icon"></i> Task ID
                                <i class="fas fa-angle-right ms-auto arrow-taskid-temanggung"></i>
                            </a>
                            <div class="collapse {{ Request::is('qltmg_TaskID*') || Request::is('qltmg_rekap_taskid*') ? 'show' : '' }}"
                                id="dropdownMenuTaskIDTemanggung">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/qltmg_TaskID') }}"
                                            class="nav-link {{ Request::is('qltmg_TaskID') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Data Task ID
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/qltmg_rekap_taskid') }}"
                                            class="nav-link {{ Request::is('qltmg_rekap_taskid') ? 'active' : '' }}">
                                            <i class="fas fa-database nav-icon"></i> Rekap Data Task ID
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</body>