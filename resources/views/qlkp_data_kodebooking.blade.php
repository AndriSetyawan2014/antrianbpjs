@extends('layouts.admin')

@section('content')
@include('partials.kodebooking-page', [
    'pageTitle' => 'Data Kode Booking — Kulon Progo',
    'filterAction' => route('qlkp_data_kodebooking'),
    'exportUrl' => route('export_qlkp_kodebooking'),
    'items' => $qlkp_data_kodebooking,
    'startDate' => $startDate ?? request('start_date', date('Y-m-d')),
    'endDate' => $endDate ?? request('end_date', date('Y-m-d')),
    'tableId' => 'qlkp_data_kodebooking',
])
@endsection
