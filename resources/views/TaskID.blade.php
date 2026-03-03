@extends('layouts.admin')

@section('content')

<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
        <form id="filterForm" action="{{ route('taskid.filter') }}" method="GET">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control d-inline-block"
                style="width: auto; display: inline-block;" value="{{ $startDate }}">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control d-inline-block"
                style="width: auto; display: inline-block;" value="{{ $endDate }}">

            <button type="submit" id="filter" class="btn btn-primary">Filter</button>
            <a href="{{ route('taskid.reset') }}" id="reset" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <!-- Task ID Filter -->
    <div class="mb-3">
        <label for="taskid_filter" class="mr-2">Task ID:</label>
        <input type="text" id="taskid_filter" class="form-control d-inline-block"
            style="width: auto; display: inline-block;">
        <button id="filter_taskid" class="btn btn-primary">Filter Task ID</button>
    </div>

    <!-- Download Excel Button -->
    <div class="mb-3">
        <button id="download_excel" class="btn btn-success">Download Excel</button>
    </div>

    <!-- Tabel -->
    <div class="table-responsive mt-4" style="max-height: 490px; overflow-y: auto;">
        <table id="data_taskid" class="table table-hover table-striped table-bordered" style="text-align: center;">
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
            <tbody>
                @foreach($TaskID as $item)
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

    <!-- Tambahkan CDN DataTables dan XLSX -->

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inisialisasi DataTables
            var table = $('#data_taskid').DataTable({
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                pageLength: 10
            });

            // Fungsi untuk memformat tanggal menjadi format ISO (YYYY-MM-DD)
            function formatDateToISO(date) {
                const dateObj = new Date(date);
                return isNaN(dateObj.getTime()) ? null : dateObj.toISOString().split('T')[0];
            }

            // Custom Filter Tanggal
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();
                var tableDate = data[7]; // Kolom tanggal (indeks 7)

                // Format tanggal tabel ke ISO
                if (tableDate) {
                    tableDate = formatDateToISO(tableDate.trim());
                }

                // Logika filter tanggal
                if (!startDate && !endDate) return true;
                if (startDate && !endDate) return tableDate >= startDate;
                if (!startDate && endDate) return tableDate <= endDate;
                if (startDate && endDate) return tableDate >= startDate && tableDate <= endDate;

                return true;
            });

            // Event untuk tombol Filter Tanggal
            $('#filter').on('click', function () {
                table.draw(); // Jalankan ulang DataTables dengan filter baru
            });

            // Event untuk tombol Reset Tanggal
            $('#reset').on('click', function () {
                $('#start_date').val('{{ date('Y-m-d') }}'); // Reset ke tanggal hari ini
                $('#end_date').val('{{ date('Y-m-d') }}'); // Reset ke tanggal hari ini
                $('#taskid_filter').val('');
                table.column(3).search('').draw();
                table.draw(); // Reset filter dan tampilkan semua data
            });

            // Event untuk tombol Filter Task ID
            $('#filter_taskid').on('click', function () {
                var taskid = $('#taskid_filter').val().trim();
                table.column(3).search(taskid).draw(); // Filter kolom Task ID (indeks 3)
            });

            // Unduh tabel ke Excel
            $('#download_excel').on('click', function () {
                var table = document.getElementById('data_taskid');
                var wb = XLSX.utils.table_to_book(table, { sheet: "Data Bridging BPJS" });

                // Simpan file Excel
                XLSX.writeFile(wb, "Data_Bridging_BPJS.xlsx");
            });

            // Set default date to today if no date is selected
            document.addEventListener('DOMContentLoaded', function () {
                var startDateInput = document.getElementById('start_date');
                var endDateInput = document.getElementById('end_date');

                // Jika input tanggal kosong, isi dengan tanggal hari ini
                if (!startDateInput.value) {
                    var today = new Date().toISOString().split('T')[0];
                    startDateInput.value = today;
                }

                if (!endDateInput.value) {
                    var today = new Date().toISOString().split('T')[0];
                    endDateInput.value = today;
                }
            });
        });
    </script>
</body>
@endsection