<style>
    /* Header */
    body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        height: 100vh;
        /* Full height of the viewport */
        width: 260px;
        background: #0C356A;
        padding-top: 20px;
        overflow-y: auto;
        /* Enable vertical scrolling */
        transition: width 0.3s ease;
        scroll-behavior: smooth;
        /* Optional: smoother scroll */
        z-index: 5;
        /* Sidebar goes below header */
    }

    .sidebar .nav {
        padding-bottom: 20px;
        /* Optional: Add padding at the bottom to avoid cut-off */
    }

    .sidebar a {
        padding: 10px 20px;
        text-decoration: none;
        font-size: 15px;
        color: #ecf0f1;
        display: block;
    }

    .sidebar a:hover {
        background-color: #ffc436;
        color: white;
    }

    .sidebar-heading {
        color: #ecf0f1;
        font-size: 15px;
        text-transform: uppercase;
        padding-left: 20px;
        margin-bottom: 10px;
    }

    .sidebar i {
        font-size: 15px;
    }

    /* Dropdown styles for rotating arrows */
    .nav-link[aria-expanded="true"] .arrow-kodebooking-jogja,
    .nav-link[aria-expanded="true"] .arrow-taskid-jogja,
    .nav-link[aria-expanded="true"] .arrow-kodebooking-kulonprogo,
    .nav-link[aria-expanded="true"] .arrow-taskid-kulonprogo {
        transform: rotate(90deg);
    }

    .arrow-kodebooking-jogja,
    .arrow-taskid-jogja,
    .arrow-kodebooking-kulonprogo,
    .arrow-taskid-kulonprogo {
        transition: transform 0.3s ease;
    }

    .nav-link.active {
        background-color: #ffc436;
        color: white;
        font-weight: bold;
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

                <a href="{{ url('/dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span class="d-none d-md-inline">Home</span>
                </a>
                <!-- Yogyakarta -->
                <a href="#"
                    class="nav-link"
                    data-bs-toggle="collapse" data-bs-target="#dropdownMenuJogja">
                    <i class="fas fa-map-marker-alt nav-icon"></i> Queen Latifa Yogyakarta
                    <i class="fas fa-angle-right ms-auto arrow-jogja"></i>
                </a>
                <div class="collapse {{ Request::is('data_kodebooking*') || Request::is('rekap_kodebooking*') || Request::is('TaskID*') || Request::is('rekap_taskid*') ? 'show' : '' }}"
                    id="dropdownMenuJogja">
                    <ul class="nav flex-column ms-4">
                        <li class="nav-item">
                            <a href="#"
                                class="nav-link"
                                data-bs-toggle="collapse" data-bs-target="#dropdownMenuKodeBookingJogja">
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
                            <a href="#"
                                class="nav-link"
                                data-bs-toggle="collapse" data-bs-target="#dropdownMenuTaskIDJogja">
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
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#dropdownMenuKulonProgo"
                    aria-expanded="false" aria-controls="dropdownMenuKulonProgo">
                    <i class="fas fa-map-marker-alt nav-icon"></i> Queen Latifa Kulon Progo
                    <i class="fas fa-angle-right ms-auto arrow-kulonprogo"></i>
                </a>
                <div class="collapse" id="dropdownMenuKulonProgo">
                    <ul class="nav flex-column ms-4">
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse"
                                data-bs-target="#dropdownMenuKodeBookingKulonProgo" aria-expanded="false"
                                aria-controls="dropdownMenuKodeBookingKulonProgo">
                                <i class="fas fa-cogs nav-icon"></i> Kode Booking
                                <i class="fas fa-angle-right ms-auto arrow-kodebooking-kulonprogo"></i>
                            </a>
                            <div class="collapse" id="dropdownMenuKodeBookingKulonProgo">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_data_kodebooking') }}" class="nav-link">
                                            <i class="fas fa-database nav-icon"></i> Data Kode Booking
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_rekap_kodebooking') }}" class="nav-link">
                                            <i class="fas fa-database nav-icon"></i> Rekap Data Kode Booking
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" data-bs-target="#dropdownMenuTaskIDKulonProgo"
                                aria-expanded="false" aria-controls="dropdownMenuTaskIDKulonProgo">
                                <i class="fas fa-tasks nav-icon"></i> Task ID
                                <i class="fas fa-angle-right ms-auto arrow-taskid-kulonprogo"></i>
                            </a>
                            <div class="collapse" id="dropdownMenuTaskIDKulonProgo">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_TaskID') }}" class="nav-link">
                                            <i class="fas fa-database nav-icon"></i> Data Task ID
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/qlkp_rekap_taskid') }}" class="nav-link">
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

    <script>
        document.getElementById('jogja-link').addEventListener('click', () => {
            const dropdown = document.getElementById('dropdownMenuJogja');
            dropdown.classList.toggle('show');
            const arrow = document.querySelector('#jogja-link .arrow');
            arrow.classList.toggle('rotate');
        });

        document.getElementById('kodebooking-link').addEventListener('click', () => {
            const dropdown = document.getElementById('dropdownMenuKodeBookingJogja');
            dropdown.classList.toggle('show');
            const arrow = document.querySelector('#kodebooking-link .arrow');
            arrow.classList.toggle('rotate');
        });

        document.getElementById('taskid-link').addEventListener('click', () => {
            const dropdown = document.getElementById('dropdownMenuTaskIDJogja');
            dropdown.classList.toggle('show');
            const arrow = document.querySelector('#taskid-link .arrow');
            arrow.classList.toggle('rotate');
        });
    </script>
</body>