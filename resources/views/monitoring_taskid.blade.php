@extends('layouts.admin')

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
