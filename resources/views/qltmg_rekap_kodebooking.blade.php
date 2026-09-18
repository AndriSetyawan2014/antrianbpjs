@extends('layouts.admin')

@section('content')
@include('partials.rekap-kodebooking-page', [
    'pageTitle' => 'Rekap Kode Booking — Temanggung',
    'routeName' => 'qltmg_rekap_kodebooking',
    'summaryItems' => $data,
    'isDetail' => isset($messageFilter),
    'detailItems' => $detailData ?? [],
    'startDate' => $startDate ?? request('start_date', date('Y-m-d')),
    'endDate' => $endDate ?? request('end_date', date('Y-m-d')),
])
@endsection
