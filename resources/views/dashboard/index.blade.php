@extends('layouts.admin')

@section('content')

<style>
    /* Styling global */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* CSS untuk search bar */
    .search-container {
        position: fixed;
        top: 40px;
        right: 40px;
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
        width: 250px;
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

    @media (max-width: 767px) {
        .search-container {
            top: 10px;
            right: 10px;
        }
    }

    /* CSS untuk dropdown */
    .dropdown {
        position: relative;
        display: inline-block;
        margin-bottom: 50px;
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

    @media (max-width: 767px) {
        .dropdown {
            margin: 10px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 10px;
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

    /* Filter tanggal Yogyakarta */
    .form-group {
        display: flex;
        align-items: center;
        margin-top: 30px;
        margin-bottom: 20px;
    }

    label {
        margin-right: 10px;
        white-space: nowrap;
    }

    #filterDateYogyakarta {
        width: 130px;
        height: 36px;
    }

    /* Filter tanggal Kulon Progo */
    .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    label {
        margin-right: 10px;
        white-space: nowrap;
    }

    #filterDateKulonProgo {
        width: 130px;
        height: 36px;
    }

    /* GRAFIK */
    .card {
        background-color: #f2f2f2;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chart-container {
        height: 250px;
        position: center;
    }

    .hidden {
        display: none;
    }

    .chart-title {
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
        color: #333;
    }
</style>

<!-- Content Wrapper -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Search bar -->
        <div class="search-container">
            <button class="search-icon">
                <i class="fa fa-search"></i>
            </button>
            <input type="text" class="search-input" placeholder="Search..." />
        </div>
        <!-- Region selector -->
        <div class="dropdown">
            <select id="regionSelector">
                <option value="yogyakarta">Queen Latifa Yogyakarta</option>
                <option value="kulonprogo">Queen Latifa Kulon Progo</option>
            </select>
        </div>
    </div>

    <!-- Yogyakarta Section -->
    <div id="yogyakarta" class="region-content">
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

         <!-- Tombol Send -->
         <div class="d-flex justify-content-end mb-4">
            <button id="sendButton" class="btn btn-primary">Kirim Pesan</button>
        </div>

        <!-- Grafik Pasien -->
        <div>
            <div class="form-group">
                <label for="filterDateYogyakarta">Selected Date:</label>
                <input type="date" id="filterDateYogyakarta" class="form-control" />
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <div class="row">
            <!-- Bar Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="chart-container">
                        <canvas id="patientChartOverall"></canvas>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="chart-container">
                        <canvas id="patientPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Data for Pie Chart
            const pieData = {
                labels: ['Kode Booking', 'Task ID'],
                datasets: [{
                    data: [{{ $totalKodebooking }}, {{ $totalTaskId }}],
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            };

            // Config for Pie Chart
            const pieConfig = {
                type: 'pie',
                data: pieData,
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 0,
                },
            };

            // Render Pie Chart
            const ctxPie = document.getElementById('patientPieChart').getContext('2d');
            new Chart(ctxPie, pieConfig);
        </script>
        <script>
            // Inisialisasi grafik kosong
            let patientChartOverall;

            function fetchDataForDate(selectedDate) {
                // Fetch data dari server berdasarkan tanggal
                fetch(`/get-patient-data?date=${selectedDate}`)
                    .then(response => response.json())
                    .then(data => {

                        // Update data grafik
                        updateChart(data.labels, data.values);
                    });
            }

            function updateChart(labels, values) {
                if (patientChartOverall) {
                    patientChartOverall.destroy(); // Hapus grafik lama sebelum mengganti data
                }

                const ctx = document.getElementById('patientChartOverall').getContext('2d');
                patientChartOverall = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: values.map((_, index) => getColor(index)), // Warna batang berbeda
                            borderWidth: 1
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
            }

            function getColor(index) {
                const colors = [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#f8f9fc', '#e0e0e0', '#b7b9cc'
                ];
                return colors[index % colors.length];
            }

            // Event listener untuk input tanggal
            document.getElementById('filterDateYogyakarta').addEventListener('change', (event) => {
                const selectedDate = event.target.value;
                if (selectedDate) {
                    fetchDataForDate(selectedDate);
                }
            });

            // Fetch initial data for today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('filterDateYogyakarta').value = today;
            fetchDataForDate(today);

            // Update chart with initial data
            updateChart(['Kode Booking', 'Task ID'], [{{ $totalKodebooking }}, {{ $totalTaskId }}]);
            // Grafik Jumlah Pasien per Tanggal
            var ctxPatientCountPerDate = document.getElementById('patientChartOverall').getContext('2d');
            new Chart(ctxPatientCountPerDate, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Jumlah Pasien per Tanggal',
                        data: [],
                        borderColor: '#42a5f5',
                        fill: false
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
                            <div class="card-value">{{ $qlkptotalKodebooking ?? 'N/A' }}</div>
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
                            <div class="card-value">{{ $qlkptotalTaskId ?? 'N/A' }}</div>
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

        <!-- Grafik Pasien -->
        <div>
            <div class="form-group">
                <label for="filterDateKulonProgo">Selected Date:</label>
                <input type="date" id="filterDateKulonProgo" class="form-control" />
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <div class="row">
            <!-- Bar Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="chart-container">
                        <canvas id="patientChartKulonProgoOverall"></canvas>
                    </div>
                </div>
            </div>
            <!-- Pie Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="chart-container">
                        <canvas id="patientPieChartKulonProgo"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Inisialisasi grafik kosong
            let patientChartKulonProgoOverall;

            // Data untuk Pie Chart
            const pieDataKulonProgo = {
                labels: ['Kode Booking', 'Task ID'],
                datasets: [{
                    data: [{{ $qlkptotalKodebooking }}, {{ $qlkptotalTaskId }}],
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            };

            // Konfigurasi untuk Pie Chart
            const pieConfigKulonProgo = {
                type: 'pie',
                data: pieDataKulonProgo,
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 0,
                },
            };

            // Render Pie Chart
            const ctxPieKulonProgo = document.getElementById('patientPieChartKulonProgo').getContext('2d');
            new Chart(ctxPieKulonProgo, pieConfigKulonProgo);

            // Fetch data berdasarkan tanggal yang dipilih
            function fetchDataForDateKulonProgo(selectedDate) {
                fetch(`/get-patient-data-kulonprogo?date=${selectedDate}`)
                    .then(response => response.json())
                    .then(data => {
                        updateChartKulonProgo(data.labels, data.values);
                    });
            }
            // Fungsi untuk memperbarui grafik
            function updateChartKulonProgo(labels, values) {
                if (patientChartKulonProgoOverall) {
                    patientChartKulonProgoOverall.destroy(); // Hapus grafik lama sebelum mengganti data
                }

                const ctx = document.getElementById('patientChartKulonProgoOverall').getContext('2d');
                const maxValue = Math.max(...values); // Cari nilai maksimum pada data
                const suggestedMax = Math.max(200, maxValue * 1.2); // Tambahkan margin 20% ke nilai maksimum

                patientChartKulonProgoOverall = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Pasien',
                            data: values,
                            backgroundColor: values.map(() => getRandomColor()), // Warna batang berbeda
                            borderWidth: 1,
                            minBarLength: 5 // Panjang minimum batang (dalam piksel)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                min: 0, // Mulai dari 0 untuk memastikan nilai kecil terlihat
                                max: suggestedMax, // Tetapkan maksimum sumbu Y
                                ticks: {
                                    stepSize: 10, // Interval antar nilai (10 per langkah)
                                    callback: function (value) {
                                        return value.toFixed(0); // Pastikan hanya angka bulat
                                    },
                                    padding: 10 // Tambahkan jarak antar angka di sumbu Y
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const value = context.raw || 0;
                                        return `Jumlah: ${value}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Fungsi untuk menghasilkan warna acak
            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '#';
                for (let i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            // Event listener untuk input tanggal
            document.getElementById('filterDateKulonProgo').addEventListener('change', (event) => {
                const selectedDate = event.target.value;
                if (selectedDate) {
                    fetchDataForDateKulonProgo(selectedDate);
                }
            });

            // Set tanggal awal (hari ini)
            const todayKulonProgo = new Date().toISOString().split('T')[0];
            document.getElementById('filterDateKulonProgo').value = todayKulonProgo;
            fetchDataForDateKulonProgo(todayKulonProgo);

            // Update chart dengan data awal
            updateChartKulonProgo(['Kode Booking', 'Task ID'], [{{ $qlkptotalKodebooking }}, {{ $qlkptotalTaskId }}]);
        </script>

        <script>
            // Fungsi untuk toggle visibility
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
@endsection