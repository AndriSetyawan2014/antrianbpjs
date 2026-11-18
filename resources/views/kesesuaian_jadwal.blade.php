@extends('layouts.admin')

@include('partials.kesesuaian-jadwal-page', [
    'branchTitle' => 'Yogyakarta',
    'filterRoute' => route('kesesuaian_jadwal'),
    'syncRoute' => route('sync_jadwal_bpjs'),
    'urlQL' => 'QLJ',
])
