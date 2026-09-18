@extends('layouts.admin')

@section('content')
@include('partials.taskid-page', [
    'pageTitle' => 'Data Task ID',
    'filterAction' => route('taskid.filter'),
    'resetUrl' => route('taskid.reset'),
    'exportUrl' => route('export_taskid'),
    'exportMode' => 'client',
    'items' => $TaskID,
    'startDate' => $startDate ?? request('start_date', date('Y-m-d')),
    'endDate' => $endDate ?? request('end_date', date('Y-m-d')),
    'tableId' => 'data_taskid',
    'urlQL' => 'QLJ',
])
@endsection
