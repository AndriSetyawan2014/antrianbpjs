@extends('layouts.admin')

@section('content')
@include('partials.taskid-page', [
    'pageTitle' => 'Data Task ID — Temanggung',
    'filterAction' => route('qltmg_taskid.filter'),
    'resetUrl' => route('qltmg_taskid.reset'),
    'exportUrl' => route('export_qltmg_taskid'),
    'exportMode' => 'server',
    'items' => $qltmg_TaskID,
    'startDate' => $startDate ?? request('start_date', date('Y-m-d')),
    'endDate' => $endDate ?? request('end_date', date('Y-m-d')),
    'tableId' => 'qltmg_table',
    'urlQL' => 'QLTMG',
])
@endsection
