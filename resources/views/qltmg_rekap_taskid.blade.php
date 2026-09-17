@extends('layouts.admin')

@section('content')

@section('page_title')
    <i class="fas fa-clipboard-list me-2"></i>Rekap Task ID — Temanggung
@endsection

<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form method="GET" action="{{ route('qltmg_rekap_taskid') }}" style="display:contents;">
        <label>Dari:</label>
        <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
        <label>Sampai:</label>
        <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
        <button type="submit" class="btn-filter btn-primary"><i class="fas fa-search"></i> Filter</button>
        <a href="{{ route('qltmg_rekap_taskid') }}" class="btn-filter btn-secondary"><i class="fas fa-undo"></i> Reset</a>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered">
        <thead><tr><th style="width:6%;">No</th><th>Pesan / Message</th><th style="width:10%;">Total</th></tr></thead>
        <tbody>
            @php $startDate=request('start_date',date('Y-m-d')); $endDate=request('end_date',date('Y-m-d')); $ct=1; @endphp
            @foreach($qltmg_TaskID as $row)
            <tr>
                <td>{{ $ct++ }}</td>
                <td><a href="{{ route('qltmg_TaskID', ['message'=>$row->message,'start_date'=>$startDate,'end_date'=>$endDate]) }}" style="color:var(--color-info);font-weight:500;"><i class="fas fa-search me-1" style="font-size:11px;"></i>{{ $row->message }}</a></td>
                <td class="text-center"><span class="status-badge badge-muted">{{ $row->total }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
