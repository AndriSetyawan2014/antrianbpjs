@extends('layouts.admin')

@section('content')
@include('partials.kodebooking-page', [
    'pageTitle' => 'Data Kode Booking',
    'filterAction' => url('/data_kodebooking'),
    'exportUrl' => url('export-kodebooking'),
    'items' => $data_kodebooking,
    'startDate' => $start_date ?? request('start_date', date('Y-m-d')),
    'endDate' => $end_date ?? request('end_date', date('Y-m-d')),
    'tableId' => 'data_kodebooking',
])
@endsection
