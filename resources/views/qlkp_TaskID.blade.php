@extends('layouts.admin')

@section('content')
@include('partials.taskid-page', [
    'pageTitle' => 'Data Task ID — Kulon Progo',
    'filterAction' => route('qlkp_taskid.filter'),
    'resetUrl' => route('qlkp_taskid.reset'),
    'exportUrl' => route('export_qlkp_taskid'),
    'exportMode' => 'server',
    'items' => $qlkp_TaskID,
    'startDate' => $startDate ?? request('start_date', date('Y-m-d')),
    'endDate' => $endDate ?? request('end_date', date('Y-m-d')),
    'tableId' => 'qlkp_table',
    'urlQL' => 'QLKP',
])
@endsection
