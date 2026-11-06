@extends('layouts.admin')

@section('page_title')
    <h1 class="page-title"><i class="fas fa-tasks me-2 text-primary"></i>{{ $pageTitle }}</h1>
@endsection

@section('page_subtitle')
    Monitoring status pengiriman Task ID ke BPJS
@endsection

@section('content')
@include('partials.monitoring-taskid-page', [
    'pageTitle' => 'Monitoring Task ID Antrian',
    'filterAction' => url('/monitoring_taskid'),
    'startDate' => $start_date ?? request('start_date', date('Y-m-d')),
    'endDate' => $end_date ?? request('end_date', date('Y-m-d')),
    'tableId' => 'monitoring_taskid_table',
    'kodebookings' => $kodebookings,
    'groupedTasks' => $groupedTasks
])
@endsection