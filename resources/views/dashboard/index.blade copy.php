@extends('layouts.admin')

@section('content')

<style>
    /* Styling global */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .content-wrapper {
        padding: 20px;
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }

    /* CSS untuk search bar */
    .search-container {
        position: fixed;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        border: 1px solid #ccc;
        border-radius: 20px;
        padding: 5px;
        transition: width 0.3s ease-in-out;
        width: 40px;
        background-color: #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }

    .search-container.active {
        width: 240px;
    }

    .search-icon {
        background-color: transparent;
        border: none;
        padding: 5px;
        color: #777;
        font-size: 18px;
    }

    .search-input {
        border: none;
        outline: none;
        font-size: 16px;
        flex-grow: 1;
        display: none;
        padding: 8px 16px;
    }

    .search-container.active .search-input {
        display: block;
    }

    /* Responsive Styles */
    @media (max-width: 767px) {
        .search-container {
            top: 10px;
            right: 10px;
        }
    }

    /* CSS untuk dropdown */
    .dropdown {
        display: inline-block;
        background-color: #ffffff;
        border-radius: 8px;
        padding: 10px 10px;
        margin: 5px;
        transition: box-shadow 0.3s;
    }

    .dropdown:hover {
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    #regionSelector {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        border: 1px solid #ccc;
        border-radius: 5px;
        outline: none;
        font-size: 15px;
        color: #333;
        background-color: transparent;
        padding: 10px 25px;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M7 10l5 5 5-5z' fill='%23333'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 13px;
    }

    #regionSelector option {
        background-color: #ffffff;
        color: #333;
    }

    /* Responsive Styles */
    @media (max-width: 767px) {
        .dropdown {
            margin: 10px;
        }
    }

    /* Responsivitas untuk perangkat kecil */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 10px;
        }

        .dashboard-header h3 {
            font-size: 1.5rem;
        }
    }

    /* Card Styles */
    .card {
        background-color: #0174be;
        border-radius: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        font-size: 25px;
        color: white;
        background-color: #0174be;
        border-radius: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
    }

    .card-icon {
        font-size: 25px;
        margin-right: 10px;
    }

    .card-content {
        text-align: right;
    }

    .card-title {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .card-value {
        font-size: 15px;
        font-weight: bold;
    }

    /* GRAFIK */
    .chart-container {
        width: 80%;
        margin: 0 auto;
    }

    .chart-buttons {
        text-align: center;
        margin-bottom: 20px;
    }

    .chart-buttons button {
        background-color: #3b82f6;
        color: white;
        border: none;
        padding: 10px 20px;
        margin: 0 5px;
        border-radius: 5px;
        cursor: pointer;
    }

    .chart-buttons button.active {
        background-color: #a855f7;
    }

    .chart-container canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .card {
        background-color: #f2f2f2;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chart-container {
        height: 300px;
        position: relative;
    }

    .hidden {
        display: none;
    }

    .chart-title {
        font-family: 'Arial', sans-serif;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
        color: #333;
    }
</style>

<!-- Content Wrapper -->
<div class="container mt-4">
    <div class="search-container">
        <button class="search-icon">
            <i class="fa fa-search"></i>
        </button>
        <input type="text" class="search-input" placeholder="Search..." />
    </div>

    <!-- Dropdown to choose between regions -->
    <div class="dropdown">
        <select id="regionSelector">
            <option value="yogyakarta">Wilayah Yogyakarta</option>
            <option value="kulonprogo">Wilayah Kulon Progo</option>
        </select>
    </div>

    <!-- Yogyakarta Section -->
    <div id="yogyakarta" class="region-content">
        <div class="container-fluid mt-4">
            <div class="row justify-content-start">
                <!-- Card 1: Data Kode Booking -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('data_kodebooking') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-bookmark"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Kode Booking</div>
                                <div class="card-value">{{ $totalKodebooking }}</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 2: Rekap Data Kode Booking -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('rekap_kodebooking') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-list"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Rekap Kode Booking</div>
                                <div class="card-value">{{ $rekapKodebooking }}</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 3: Data Task ID -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('TaskID') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-tasks"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Task ID</div>
                                <div class="card-value">{{ $totalTaskId }}</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 4: Rekap Data Task ID -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('rekap_taskid') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-list"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Rekap Task ID</div>
                                <div class="card-value">{{ $rekapTaskId }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-8">
                    <h3 class="chart-title">Grafik Pasien Yogyakarta</h3>
                    <div class="card">
                        <div class="chart-container">
                            <canvas id="patientChartYogyakarta"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h3 class="chart-title">Jumlah Pasien Yogyakarta</h3>
                    <div class="card">
                        <div class="chart-container">
                            <canvas id="totalPatientCountYogyakarta"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Grafik Pasien Yogyakarta
                var ctxPatientYogyakarta = document.getElementById('patientChartYogyakarta').getContext('2d');
                new Chart(ctxPatientYogyakarta, {
                    type: 'line',
                    data: {
                        labels: ['Kode Booking', 'Task ID'],
                        datasets: [{
                            label: 'Pasien',
                            data: [5390, 5550, 5620, 5730, 5820, 5900, 6020, 6120, 6210, 6290, 6360, 6420],
                            borderColor: '#5c6bc0',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Grafik Jumlah Pasien Yogyakarta
                var ctxTotalPatientCountYogyakarta = document.getElementById('totalPatientCountYogyakarta').getContext('2d');
                new Chart(ctxTotalPatientCountYogyakarta, {
                    type: 'bar',
                    data: {
                        labels: ['Terkirim', 'Gagal'],
                        datasets: [{
                            label: 'Jumlah Pasien',
                            data: [200, 50],
                            backgroundColor: ['#66bb6a', '#ffa726']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
        </div>
    </div>

    <!-- Kulon Progo Section -->
    <div id="kulonprogo" class="region-content hidden">
        <div class="container-fluid mt-4">
            <div class="row justify-content-start">
                <!-- Card 1: Data Kode Booking -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('qlkp_data_kodebooking') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-bookmark"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Kode Booking</div>
                                <div class="card-value">{{ $qlkptotalKodebooking }}</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 2: Rekap Data Kode Booking -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('qlkp_rekap_kodebooking') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-list"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Rekap Kode Booking</div>
                                <div class="card-value">{{ $qlkprekapKodebooking }}</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 3: Data Task ID -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('qlkp_TaskID') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-tasks"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Task ID</div>
                                <div class="card-value">{{ $qlkptotalTaskId }}</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 4: Rekap Data Task ID -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="{{ route('qlkp_rekap_taskid') }}" class="text-decoration-none">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fa fa-list"></i>
                            </div>
                            <div class="card-content">
                                <div class="card-title">Rekap Task ID</div>
                                <div class="card-value">{{ $qlkprekapTaskId }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-8">
                    <h3 class="chart-title">Grafik Pasien Kulon Progo</h3>
                    <div class="card">
                        <div class="chart-container">
                            <canvas id="patientChartKulonProgo"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h3 class="chart-title">Jumlah Pasien Kulon Progo</h3>
                    <div class="card">
                        <div class="chart-container">
                            <canvas id="totalPatientChartKulonProgo"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Grafik Pasien untuk Kulon Progo
                var ctxPatientKulonProgo = document.getElementById('patientChartKulonProgo').getContext('2d');
                new Chart(ctxPatientKulonProgo, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Pasien',
                            data: [5390, 5550, 5620, 5730, 5820, 5900, 6020, 6120, 6210, 6290, 6360, 6420],
                            borderColor: '#5c6bc0',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Grafik Jumlah Pasien Kulon Progo
                var ctxTotalPatientCountKulonProgo = document.getElementById('totalPatientChartKulonProgo').getContext('2d');
                new Chart(ctxTotalPatientCountKulonProgo, {
                    type: 'bar',
                    data: {
                        labels: ['Terkirim', 'Gagal'],
                        datasets: [{
                            label: 'Jumlah Pasien',
                            data: [200, 50],
                            backgroundColor: ['#66bb6a', '#ffa726']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Toggle visibility based on dropdown selection
                document.getElementById('regionSelector').addEventListener('change', function () {
                    var selectedRegion = this.value;
                    document.querySelectorAll('.region-content').forEach(function (element) {
                        element.classList.add('hidden');
                    });
                    document.getElementById(selectedRegion).classList.remove('hidden');
                });

                // Search bar functionality
                const searchContainer = document.querySelector('.search-container');
                const searchIcon = document.querySelector('.search-icon');
                const searchInput = document.querySelector('.search-input');

                searchContainer.addEventListener('click', () => {
                    searchContainer.classList.toggle('active');
                    if (searchContainer.classList.contains('active')) {
                        searchInput.focus();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!searchContainer.contains(event.target)) {
                        searchContainer.classList.remove('active');
                    }
                });

                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase();
                    const cards = document.querySelectorAll('.card-body');

                    cards.forEach(card => {
                        const title = card.querySelector('.card-title').textContent.toLowerCase();
                        if (title.includes(query)) {
                            card.parentElement.style.display = '';
                        } else {
                            card.parentElement.style.display = 'none';
                        }
                    });
                });
            </script>
        </div>
    </div>
</div>

@endsection
