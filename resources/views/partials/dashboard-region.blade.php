{{-- Partial DRY: 1 region dashboard (stat cards + charts).
  Params: $regionId, $hidden, $chartSuffix,
          $routeKodebooking, $routeRekapKodebooking, $routeTaskid, $routeRekapTaskid,
          $countKodebooking, $countRekapKodebooking, $countTaskid, $countRekapTaskid,
          $barId, $pieId
--}}
<div id="{{ $regionId }}" class="region-content{{ ($hidden ?? false) ? ' hidden' : '' }}">

    {{-- Stat Cards --}}
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ $routeKodebooking }}" class="stat-card card-kode-booking">
                <div class="stat-card-label"><i class="fas fa-calendar-check me-1"></i>Kode Booking</div>
                <div class="stat-card-value">{{ $countKodebooking }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-calendar-check"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ $routeRekapKodebooking }}" class="stat-card card-rekap-booking">
                <div class="stat-card-label"><i class="fas fa-chart-bar me-1"></i>Rekap Kode Booking</div>
                <div class="stat-card-value">{{ $countRekapKodebooking }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-chart-bar"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ $routeTaskid }}" class="stat-card card-task-id">
                <div class="stat-card-label"><i class="fas fa-tasks me-1"></i>Task ID</div>
                <div class="stat-card-value">{{ $countTaskid }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-tasks"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ $routeRekapTaskid }}" class="stat-card card-rekap-taskid">
                <div class="stat-card-label"><i class="fas fa-clipboard-list me-1"></i>Rekap Task ID</div>
                <div class="stat-card-value">{{ $countRekapTaskid }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-clipboard-list"></i></div>
            </a>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row">
        <div class="col-md-7">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-bar"></i> Jumlah Pasien per Poli{{ $chartSuffix }}
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="{{ $barId }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-pie"></i> Distribusi Data
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="{{ $pieId }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
