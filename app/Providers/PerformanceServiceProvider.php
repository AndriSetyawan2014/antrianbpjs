<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PerformanceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootQueryLogging();
        $this->bootDashboardCache();
    }

    /**
     * Enable slow query logging in development
     */
    protected function bootQueryLogging(): void
    {
        if (app()->environment('local', 'development')) {
            DB::listen(function ($query) {
                if ($query->time > 100) { // Log queries slower than 100ms
                    Log::warning('Slow Query Detected', [
                        'time' => round($query->time, 2) . 'ms',
                        'sql' => Str::replaceArray('?', $query->bindings, $query->sql),
                        'bindings' => $query->bindings,
                    ]);
                }
            });
        }
    }

    /**
     * Setup dashboard cache for expensive stats
     */
    protected function bootDashboardCache(): void
    {
        // This will be called from controllers to cache dashboard stats
    }

    /**
     * Get cached dashboard stats
     */
    public static function getCachedDashboardStats(string $region, callable $callback): array
    {
        $cacheKey = "dashboard_stats:{$region}";
        $ttl = 300; // 5 minutes

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear dashboard cache for specific region
     */
    public static function clearDashboardCache(string $region = null): void
    {
        if ($region) {
            Cache::forget("dashboard_stats:{$region}");
        } else {
            Cache::forgetMatching('dashboard_stats:*');
        }
    }

    /**
     * Get cached VClaim summary per QL
     */
    public static function getCachedVClaimSummary(string $kodeQL, string $tanggal, callable $callback): array
    {
        $cacheKey = "vclaim_summary:{$kodeQL}:{$tanggal}";
        $ttl = 600; // 10 minutes

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear VClaim summary cache
     */
    public static function clearVClaimCache(string $kodeQL = null, string $tanggal = null): void
    {
        if ($kodeQL && $tanggal) {
            Cache::forget("vclaim_summary:{$kodeQL}:{$tanggal}");
        } elseif ($kodeQL) {
            Cache::forgetMatching("vclaim_summary:{$kodeQL}:*");
        } else {
            Cache::forgetMatching('vclaim_summary:*');
        }
    }
}
