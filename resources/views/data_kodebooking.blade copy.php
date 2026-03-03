@extends('layouts.admin')

@section('content')

<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* Mengatur font untuk seluruh body */
            font-size: 14px;
            /* Mengatur ukuran font untuk body */
        }

        h3 {
            font-size: 30px !important;
            /* Ukuran font untuk h3 */
            margin-bottom: 5px !important;
            /* Mengurangi jarak bawah h3 */
            font-weight: bold !important;
            /* Pastikan h3 tetap bold */
            text-align: left !important;
            /* Rata kiri */
        }

        h4 {
            font-size: 25px !important;
            /* Ukuran font untuk h4 */
            margin-bottom: 5px !important;
            /* Mengurangi jarak bawah h4 */
            font-weight: normal !important;
            /* Pastikan h4 tidak bold */
            text-align: left !important;
            /* Rata kiri */
        }

        .table {
            font-family: 'Poppins', sans-serif;
            /* Mengatur font untuk tabel */
            font-size: 14px;
            /* Ukuran font untuk tabel */
        }

        .table th,
        .table td {
            font-size: 14px;
            /* Ukuran font untuk sel tabel */
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }


        .table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Mengatur ukuran font dan kotak input tanggal */
        input[type="date"] {
            font-size: 14px;
            /* Ukuran font untuk input tanggal */
            padding: 2px;
            /* Padding untuk input tanggal */
            width: 20px;
            /* Mengatur lebar sesuai kebutuhan */
            height: 25px;
            /* Mengatur tinggi kotak input */
        }

        /* Mengatur ukuran font dan kotak untuk tombol */
        button {
            font-size: 14px !important;
            /* Ukuran font untuk tombol */
            padding: 2px 5px !important;
            /* Padding untuk tombol */
            height: auto;
            /* Mengatur tinggi otomatis untuk tombol */
        }

        .mb-3 {
            margin-bottom: 3px;
            /* Mengurangi jarak antara input tanggal dan tabel */
        }

        .dataTables_wrapper {
            margin-bottom: 10px;
        }

        .dataTables_wrapper {
            position: sticky;
            top: 0;
            z-index: 5;
            /* Pastikan di atas tabel */
        }

        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="display-6 font-weight-bold text-primary" style="text-align: left;">
                Sistem Pemantauan Data Bridging BPJS
            </h3>
            <!-- <h4 class="font-weight-medium text-secondary" style="color: #6c757d;">
                Rumah Sakit Queen Latifa
            </h4> -->
        </div>
    </div>
</div>

<!-- Input Tanggal -->
<div class="mb-3">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" class="form-control d-inline-block" style="width: auto; display: inline-block;">

    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" class="form-control d-inline-block" style="width: auto; display: inline-block;">

    <button id="filter" class="btn btn-primary">Filter</button>
    <button id="reset" class="btn btn-secondary">Reset</button>
</div>

<!-- download exel -->
<div class="mb-3">
    <button id="export" class="btn btn-success">Download Excel</button>
</div>

<!-- Tabel -->
<div class="table-responsive mt-4" style="max-height: 450px; overflow-y: auto;">
    <table id="data_kodebooking" class="table table-hover table-striped table-bordered" style="text-align: center;">
        <thead class="thead-dark bg-primary text-white sticky-top">
            <tr>
                <th style="width: 3px;">No</th>
                <th style="width: 15px;">No RM</th>
                <th style="width: 50px;">Tanggal Periksa</th>
                <th style="width: 30px;">Code</th>
                <th style="width: 30px;">Message</th>
                <th style="width: 250px;">Request</th>
                <th style="width: 50px;">Response</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data_kodebooking as $item)
                <tr>
                    <td></td> <!-- DataTables will handle numbering -->
                    <td>{{ $item->norm }}</td>
                    <td>{{ $item->tanggalperiksa }}</td>
                    <td>{{ $item->code }}</td>
                    <td>{{ json_decode($item->response)->metadata->message ?? '' }}</td>
                    <td style="text-align: left;">{{$item->request }}</td>
                    <td style="text-align: left;">{{$item->response }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
    // Set default value for start date to today
    var today = new Date().toISOString().split('T')[0]; // Mendapatkan tanggal hari ini dalam format YYYY-MM-DD
    $('#start_date').val(today);
    $('#end_date').val(today);

        $('#data_kodebooking').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1; // Nomor urut
                    }
                },
                { data: 'norm', name: 'norm' },
                { data: 'tanggalperiksa', name: 'tanggalperiksa' },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data) {
                        return data ? data : 'NULL';
                    }
                },
                {
                    data: 'message',
                    name: 'message',
                    render: function (data) {
                        return data ? data : 'NULL';
                    }
                },
                {
                    data: 'request',
                    render: function (data) {
                        try {
                            console.log("Request Data:", data); // Debug JSON request
                            return `<pre>${JSON.stringify(JSON.parse(data), null, 2)}</pre>`;
                        } catch (e) {
                            console.error("Error parsing request:", e);
                            return 'NULL';
                        }
                    }
                },
                {
                    data: 'response',
                    render: function (data) {
                        try {
                            console.log("Response Data:", data); // Debug JSON response
                            return `<pre>${JSON.stringify(JSON.parse(data), null, 2)}</pre>`;
                        } catch (e) {
                            console.error("Error parsing response:", e);
                            return 'NULL';
                        }
                    }
                }
            ],
            drawCallback: function () {
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();
                var dataFound = false; // Flag untuk mengecek apakah ada data

                this.api().rows().every(function () {
                    var data = this.data();
                    var tanggalPeriksa = data.tanggalperiksa;

                    if (startDate && endDate) {
                        if (tanggalPeriksa >= startDate && tanggalPeriksa <= endDate) {
                            $(this.node()).show();
                            dataFound = true; // Jika ada data, set flag ke true
                        } else {
                            $(this.node()).hide();
                        }
                    } else {
                        $(this.node()).show();
                        dataFound = true; // Jika tidak ada filter, set flag ke true
                    }
                });

                // Jika tidak ada data yang ditemukan
                if (!dataFound) {
                    $('#data_kodebooking tbody').html('<tr><td colspan="7">Tidak ada data yang ditemukan.</td></tr>');
                }
            },
        });

        // Filter function
        $('#filter').click(function () {
            $('#loading').show(); // Tampilkan loading
            table.draw();
            $('#loading').hide(); // Sembunyikan loading setelah draw
        });

        // Reset function
        $('#reset').click(function () {
            $('#start_date').val('');
            $('#end_date').val('');
            table.draw();
        });
        // Cek apakah ada parameter "message" di URL
    var urlParams = new URLSearchParams(window.location.search);
    var messageFilter = urlParams.get('message');

    if (messageFilter) {
        table.columns(4).search(messageFilter).draw(); // Kolom ke-4 adalah kolom 'message'
    }
    });

    document.getElementById('export').addEventListener('click', function() {
    window.location.href = '{{ route('export_kodebooking') }}';
});

</script>

@endsection
