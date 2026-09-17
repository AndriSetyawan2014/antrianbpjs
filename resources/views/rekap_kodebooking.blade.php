@extends('layouts.admin')

@section('content')

@section('page_title')
    <i class="fas fa-chart-bar me-2"></i>Rekap Data Kode Booking
@endsection

{{-- Filter Card --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form method="GET" action="{{ route('rekap_kodebooking') }}" style="display:contents;">
        <label>Dari:</label>
        <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
        <label>Sampai:</label>
        <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route('rekap_kodebooking') }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>
    @if(isset($messageFilter))
    <a href="{{ route('rekap_kodebooking', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="btn-filter btn-secondary ms-auto">
        <i class="fas fa-arrow-left"></i> Kembali ke Rekap
    </a>
    @endif
</div>

{{-- Tabel --}}
<div class="table-responsive">
    @if(isset($messageFilter))
    {{-- Detail per message --}}
    <table class="table table-hover table-striped table-bordered">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:8%;">No RM</th>
                <th style="width:10%;">Tgl Periksa</th>
                <th style="width:7%;">Code</th>
                <th style="width:16%;">Message</th>
                <th style="width:27%;">Request</th>
                <th style="width:28%;">Response</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detailData as $index => $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200' : ($code == 0 ? '⏳' : '✗ '.$code);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->norm }}</td>
                <td>{{ $item->tanggalperiksa }}</td>
                <td class="text-center">
                    <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
                <td>{{ json_decode($item->response)->metadata->message ?? '' }}</td>
                <td class="text-center">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($item->request) }}"
                            data-title="Request — No RM {{ $item->norm }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($item->response) }}"
                            data-title="Response — No RM {{ $item->norm }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @else
    {{-- Rekap summary --}}
    <table class="table table-hover table-striped table-bordered">
        <thead>
            <tr>
                <th style="width:6%;">No</th>
                <th>Pesan / Message</th>
                <th style="width:10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <a href="{{ route('rekap_kodebooking', ['start_date' => $startDate, 'end_date' => $endDate, 'message' => $item->message_all]) }}"
                       style="color:var(--color-info); font-weight:500;">
                        <i class="fas fa-search me-1" style="font-size:11px;"></i>{{ $item->message_all }}
                    </a>
                </td>
                <td class="text-center">
                    <span class="status-badge badge-muted">{{ $item->total }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection