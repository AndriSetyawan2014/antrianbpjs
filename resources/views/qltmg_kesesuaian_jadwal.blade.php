@extends('layouts.admin')

@include('partials.kesesuaian-jadwal-page', [
    'branchTitle' => 'Temanggung',
    'filterRoute' => route('qltmg_kesesuaian_jadwal'),
    'syncRoute' => route('qltmg_sync_jadwal_bpjs'),
    'urlQL' => 'QLTMG',
])
