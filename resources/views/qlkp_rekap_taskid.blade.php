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

    <!-- Filter by Date Form -->
    <form method="GET" action="{{ route('qlkp_rekap_taskid') }}">
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('qlkp_rekap_taskid') }}" class="btn btn-secondary">Reset</a>
    </form>

    <style>
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

    <!-- Tabel Rekap -->
    <div class="table-responsive mt-4" style="max-height: 560px; overflow-y: auto;">
        <table id="qlkp_data_taskids" class="table table-hover table-striped table-bordered">
            <thead class="thead-dark bg-primary text-white sticky-top">
                <tr>
                    <th style="text-align: center;">No</th>
                    <th style="text-align: center;">Message</th>
                    <th style="text-align: center;">Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($qlkp_TaskID as $row)
                    <tr>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: center;">
                            {{ $loop->iteration }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: left;">
                            <a href="{{ route('qlkp_taskid', ['message' => $row->message, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-primary">
                                {{ $row->message }}
                            </a>
                        </td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: center;">
                            {{ $row->total }}</td>
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
        var filterDateUrl = '{{ route("qlkp_rekap_taskid") }}';
    </script>
    <script src="{{ asset('js/rekaptaskid.js') }}"></script>
</body>
@endsection