@extends('layouts.admin')

@section('content')

<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap" rel="stylesheet">
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
    <div class="mb-3">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" class="form-control d-inline-block"
            style="width: auto; display: inline-block;" value="{{ $start_date }}">

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" class="form-control d-inline-block"
            style="width: auto; display: inline-block;" value="{{ $end_date }}">

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

    <!-- Download Excel Button -->
    <div class="mb-3">
        <a href="{{ url('export-kodebooking') }}" class="btn btn-success">Download Excel</a>
    </div>

    <!-- Tabel -->
    <div class="table-responsive mt-4" style="max-height: 490px; overflow-y: auto;">
        <table id="data_kodebooking" class="table table-hover table-striped table-bordered" style="text-align: center;">
            <thead class="thead-dark bg-primary text-white sticky-top">
                <tr>
                    <th style="width: 5px; text-align: center;">No</th>
                    <th style="width: 15px; text-align: center;">No RM</th>
                    <th style="width: 50px; text-align: center;">Check Date</th>
                    <th style="width: 30px; text-align: center;">Code</th>
                    <th style="width: 30px; text-align: center;">Message</th>
                    <th style="width: 250px; text-align: center;">Request</th>
                    <th style="width: 50px; text-align: center;">Response</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data_kodebooking as $item)
                    <tr>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;"></td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->norm }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->tanggalperiksa }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->code }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ json_decode($item->response)->metadata->message ?? '' }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: left;">{{$item->request }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: left;">{{$item->response }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function () {
        var table = $('#data_kodebooking').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'norm', name: 'norm' },
            { data: 'tanggalperiksa', name: 'tanggalperiksa' },
            { data: 'code', name: 'code' },
            { data: 'message', name: 'message' },
            {
                data: 'request',
                render: function (data) {
                    try {
                        var parsedData = JSON.parse(data);
                        return $('<pre></pre>').text(JSON.stringify(parsedData, null, 2)).prop('outerHTML');
                    } catch (e) {
                        return 'NULL';
                    }
                }
            },
            {
                data: 'response',
                render: function (data) {
                    try {
                        var parsedData = JSON.parse(data);
                        return $('<pre></pre>').text(JSON.stringify(parsedData, null, 2)).prop('outerHTML');
                    } catch (e) {
                        return 'NULL';
                    }
                }
            }
        ]
    });

    // Filter button click event
    $('#filter').click(function () {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        window.location.href = `?start_date=${startDate}&end_date=${endDate}`;
    });

    // Reset button click event
    $('#reset').click(function () {
        var today = new Date().toISOString().split('T')[0];
        $('#start_date').val(today);
        $('#end_date').val(today);
        $('#filter').click();
    });
    });
    </script>
</body>
@endsection