@extends('layouts.admin')

@section('content')
@include('partials.rekap-taskid-page', [
    'pageTitle' => 'Rekap Task ID — Kulon Progo',
    'filterAction' => route('qlkp_rekap_taskid'),
    'resetUrl' => route('qlkp_rekap_taskid'),
    'detailRoute' => 'qlkp_TaskID',
    'items' => $qlkp_TaskID,
])
@endsection
