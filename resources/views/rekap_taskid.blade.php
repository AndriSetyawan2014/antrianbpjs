@extends('layouts.admin')

@section('content')
@include('partials.rekap-taskid-page', [
    'pageTitle' => 'Rekap Data Task ID',
    'filterAction' => route('rekap_taskid'),
    'resetUrl' => route('rekap_taskid'),
    'detailRoute' => 'TaskID',
    'items' => $TaskID,
])
@endsection
