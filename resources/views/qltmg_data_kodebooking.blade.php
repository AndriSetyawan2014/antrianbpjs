@extends('layouts.admin')

@section('content')
@include('partials.kodebooking-page', [
    'pageTitle' => 'Data Kode Booking — Temanggung',
    'filterAction' => route('qltmg_data_kodebooking'),
    'exportUrl' => route('export_qltmg_kodebooking'),
    'items' => $qltmg_data_kodebooking,
    'startDate' => $startDate ?? request('start_date', date('Y-m-d')),
    'endDate' => $endDate ?? request('end_date', date('Y-m-d')),
    'tableId' => 'qltmg_data_kodebooking',
])
@endsection
