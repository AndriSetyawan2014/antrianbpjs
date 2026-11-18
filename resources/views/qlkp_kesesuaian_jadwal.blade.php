@extends('layouts.admin')

@include('partials.kesesuaian-jadwal-page', [
    'branchTitle' => 'Kulon Progo',
    'filterRoute' => route('qlkp_kesesuaian_jadwal'),
    'syncRoute' => route('qlkp_sync_jadwal_bpjs'),
    'urlQL' => 'QLKP',
])
