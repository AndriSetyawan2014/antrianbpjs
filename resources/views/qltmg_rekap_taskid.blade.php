@extends('layouts.admin')

@section('content')
@include('partials.rekap-taskid-page', [
    'pageTitle' => 'Rekap Task ID — Temanggung',
    'filterAction' => route('qltmg_rekap_taskid'),
    'resetUrl' => route('qltmg_rekap_taskid'),
    'detailRoute' => 'qltmg_TaskID',
    'items' => $qltmg_TaskID,
])
@endsection
