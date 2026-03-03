@extends('layouts.admin')

@section('content')

<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap" rel="stylesheet">
</head>

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

    <!-- Form untuk memilih tanggal -->
    <form method="GET" action="{{ route('qltmg_rekap_kodebooking') }}">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" value="{{ old('start_date', request('start_date', date('Y-m-d'))) }}">

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" value="{{ old('end_date', request('end_date', date('Y-m-d'))) }}">

    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('qltmg_rekap_kodebooking') }}" class="btn btn-secondary">Reset</a>
</form>

    @if(isset($messageFilter))
        <!-- Button untuk kembali ke halaman sebelumnya -->
        <a href="{{ route('qltmg_rekap_kodebooking', ['filter_date' => request('filter_date')]) }}" class="btn btn-secondary">Kembali</a>
        <!-- Table displaying the data -->
        <div class="mt-4">
            <table class="table table-hover table-striped table-bordered">
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
                    @foreach($detailData as $index => $item)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->norm }}</td>
                            <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->tanggalperiksa }}</td>
                            <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">{{ $item->code }}</td>
                            <td style="font-family: 'Poppins', sans-serif; font-size: 14px;">
                                {{ json_decode($item->response)->metadata->message ?? '' }}
                            </td>
                            <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: left;">
                                <pre>{{ json_encode(json_decode($item->request), JSON_PRETTY_PRINT) }}</pre>
                            </td>
                            <td style="font-family: 'Poppins', sans-serif; font-size: 14px; text-align: left;">
                                <pre>{{ json_encode(json_decode($item->response), JSON_PRETTY_PRINT) }}</pre>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- Table displaying the data -->
        <div class="table-responsive mt-4">
            <table class="table table-hover table-striped table-bordered">
                <thead class="thead-dark bg-primary text-white sticky-top">
                    <tr>
                        <th>No</th>
                        <th>Message</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('qltmg_rekap_kodebooking', ['filter_date' => request('filter_date'), 'message' => $item->message_all]) }}"
                                    class="text-primary">
                                    {{ $item->message_all }}
                                </a>
                            </td>
                            <td>{{ $item->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @endsection
