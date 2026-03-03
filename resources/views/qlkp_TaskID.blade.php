@extends('layouts.admin')

@section('content')

<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap" rel="stylesheet">
    <!-- <link re</body>l="stylesheet" href="{{ asset('css/datakodebooking.css') }}"> -->
</head>

<body>
    <h1> Sistem Pemantauan Data Bridging BPJS </h1>
    <style>
        h1 {
            text-align: left;
            font-family: 'Poppins', sans-serif;
            font-weight: 550;
            margin-top: 10px;
            margin-bottom: 10px;
            color: #007bff;
        }
    </style>

    <!-- Input Tanggal -->
    <div class="mb-3">
        <input type="date" id="start_date" class="form-control d-inline-block" 
            style="width: auto; display: inline-block;" value="{{ $startDate }}">
        <input type="date" id="end_date" class="form-control d-inline-block" 
            style="width: auto; display: inline-block;" value="{{ $endDate }}">
        <button id="filter" class="btn btn-primary">Filter</button>
        <button id="reset" class="btn btn-secondary">Reset</button>
    </div>
    <style>
        .mb-3 {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        label {
            font-weight: 500;
            margin-right: 10px;
            color: #333;
        }

        input[type="date"] {
            border: 1px solid #007bff;
            border-radius: 6px;
            padding: 5px 10px;
            margin-right: 10px;
            font-size: 0.9rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="date"]:focus {
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        .btn {
            padding: 3px 8px;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 8px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
    </style>

    <script>
        document.getElementById('filter').addEventListener('click', function () {
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;
            var url = new URL(window.location.href);
            var params = new URLSearchParams(url.search);

            // Tambahkan parameter tanggal ke URL
            if (startDate) params.set('start_date', startDate);
            if (endDate) params.set('end_date', endDate);

            // Redirect dengan parameter baru
            window.location.href = url.pathname + '?' + params.toString();
        });

        document.getElementById('reset').addEventListener('click', function () {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            $('#taskid_filter').val(''); // Reset filter Task ID
            var rows = document.querySelectorAll('#taskid-body tr');
            rows.forEach(function (row) {
                row.style.display = ''; // Tampilkan semua baris
            });

            // Reset DataTables search
            $('#qlkp_table').DataTable().search('').draw();
        });

        // Set default date to today
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').setAttribute('max', today);
        document.getElementById('end_date').setAttribute('max', today);
    </script>

    <!-- Task ID Filter -->
    <div class="mb-3">
        <label for="taskid_filter" class="mr-2">Task ID:</label>
        <input type="text" id="taskid_filter" class="form-control d-inline-block"
            style="width: auto; display: inline-block;">
        <button id="filter_taskid" class="btn btn-primary">Filter Task ID</button>
    </div>

    <!-- Download Excel Button -->
    <div class="mb-3">
        <a href="{{ route('export_qlkp_taskid') }}" class="btn btn-success">Download Excel</a>
    </div>

    <!-- Tabel -->
    <div class="table-responsive mt-4" style="max-height: 490px; overflow-y: auto;">
        <table id="qlkp_table" class="table table-hover table-striped table-bordered" style="text-align: center;">
            <thead class="thead-dark bg-primary text-white sticky-top">
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 10%;">Kode Booking</th>
                    <th style="width: 10%;">Waktu</th>
                    <th style="width: 10%;">Task ID</th>
                    <th style="width: 10%;">ID Pendaftaran</th>
                    <th style="width: 10%;">Code</th>
                    <th style="width: 10%;">Message</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 10%;">Jam</th>
                    <th style="width: 10%;">Request</th>
                    <th style="width: 10%;">Response</th>
                    <th style="width: 5%;">Reupload</th>
                </tr>
            </thead>
            <tbody id="taskid-body">
                @foreach($qlkp_TaskID as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->kodebooking }}</td>
                        <td>{{ $item->waktu }}</td>
                        <td>{{ $item->taskid }}</td>
                        <td>{{ $item->idpendaftaran }}</td>
                        <td>{{ $item->code }}</td>
                        <td>{{ json_decode($item->response)->metadata->message ?? '' }}</td>
                        <td>{{ $item->tanggal }}</td>
                        <td>{{ $item->jam }}</td>
                        <td style="text-align: left;">
                            <pre>{{ json_encode(json_decode($item->request), JSON_PRETTY_PRINT) }}</pre>
                        </td>
                        <td style="text-align: left;">
                            <pre>{{ json_encode(json_decode($item->response), JSON_PRETTY_PRINT) }}</pre>
                        </td>
                        <td style="text-align: left;">{{ $item->reupload }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Tambahkan CDN DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inisialisasi DataTables
            var table = $('#qlkp_table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 10
            });

            // Fungsi untuk memformat tanggal menjadi format yang konsisten (YYYY-MM-DD)
            function formatDateToISO(date) {
                const dateObj = new Date(date);
                if (isNaN(dateObj.getTime())) return null; 
                return dateObj.toISOString().split('T')[0];
            }

            // Custom Filter Tanggal
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();
                var tableDate = data[7];

                if (tableDate) {
                    tableDate = formatDateToISO(tableDate.trim());
                }

                if (!startDate && !endDate) {
                    return true;
                }

                if (startDate && !endDate) {
                    return tableDate >= startDate;
                }

                if (!startDate && endDate) {
                    return tableDate <= endDate;
                }

                if (startDate && endDate) {
                    return tableDate >= startDate && tableDate <= endDate;
                }

                return true;
            });

            // Event untuk tombol Filter
            $('#filter').on('click', function () {
                table.draw();
            });

            // Event untuk tombol Reset
            $('#reset').on('click', function () {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#taskid_filter').val('');
                table.draw();
            });

            // Event untuk tombol Filter Task ID
            $('#filter_taskid').on('click', function () {
                var taskid = $('#taskid_filter').val().trim().toLowerCase();
                table.column(3).search(taskid).draw();
            });
        });
    </script>
</body>
@endsection
