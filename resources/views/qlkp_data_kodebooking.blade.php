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
        <form id="filterForm" action="{{ route('qlkp_data_kodebooking') }}" method="GET">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control d-inline-block"
                style="width: auto; display: inline-block;" value="{{ $startDate }}">

            <input type="date" id="end_date" name="end_date" class="form-control d-inline-block"
                style="width: auto; display: inline-block;" value="{{ $endDate }}">

            <button type="submit" id="filter" class="btn btn-primary">Filter</button>
            <a href="{{ route('qlkp_data_kodebooking') }}" id="reset" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div class="mb-3">
        <button class="btn btn-success">
            <a href="{{ route('export_qlkp_kodebooking') }}" style="color: white; text-decoration: none;">Download
                Excel</a>
        </button>
    </div>

    <div class="table-responsive mt-4" style="max-height: 490px; overflow-y: auto;">
        <table id="qlkp_data_kodebooking" class="table table-hover table-striped table-bordered text-center">
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
                @foreach($qlkp_data_kodebooking as $item)
                    <tr>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">
                            {{ $loop->iteration }}
                        </td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->norm }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->tanggalperiksa }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->code }}</td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">
                            @php
                                $response = json_decode($item->response);
                            @endphp
                            {{ $response->metadata->message ?? 'No message available' }}
                        </td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: left;">
                            {!! nl2br(e($item->request)) !!}
                        </td>
                        <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: left;">
                            {!! nl2br(e($item->response)) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- CSS DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
    var table = $('#qlkp_data_kodebooking').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        columns: [
            { data: null, render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }},
            { data: 'norm' },
            { data: 'tanggalperiksa' },
            { data: 'code', render: function (data) { return data ? data : 'NULL'; }},
            { data: 'message', render: function (data) { return data ? data : 'NULL'; }},
            { data: 'request', render: function (data) { return formatJson(data); }},
            { data: 'response', render: function (data) { return formatJson(data); }}
        ],
    });

    function formatJson(data) {
        try {
            var parsedData = JSON.parse(data);
            return $('<pre></pre>').text(JSON.stringify(parsedData, null, 2)).prop('outerHTML');
        } catch (e) {
            console.error("Error parsing JSON:", e);
            return 'NULL';
        }
    }

    // Custom filter function for date range
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var date = data[2]; // Use data for the date column

            if (
                (startDate === "" && endDate === "") ||
                (startDate === "" && date <= endDate) ||
                (startDate <= date && endDate === "") ||
                (startDate <= date && date <= endDate)
            ) {
                return true;
            }
            return false;
        }
    );

    table.draw();

    $('#filter').click(function () {
        table.draw();
    });

    $('#reset').click(function () {
        $('#start_date').val('{{ $startDate }}');
        $('#end_date').val('{{ $endDate }}');
        table.draw();
    });
});

    </script>
</body>

@endsection