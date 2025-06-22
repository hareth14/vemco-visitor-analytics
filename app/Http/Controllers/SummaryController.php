<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\Sensor;
use Carbon\Carbon;
use App\Helpers\CacheHelper;

class SummaryController extends Controller
{
    // GET /api/summary
    public function index()
    {
        $cacheKey = 'summary_dashboard';
        // Use CacheHelper to handle caching with fallback

        $summary = CacheHelper::rememberWithFallback(
            $cacheKey,
            ['summary'],
            'very_high_freq',
            function () {
                $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

                $totalVisitors = Visitor::where('date', '>=', $sevenDaysAgo)->sum('count');
                $sensorCounts = Sensor::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status');

                return [
                    'total_visitors_last_7_days' => (int)$totalVisitors,
                    'active_sensors'             => $sensorCounts['active'] ?? 0,
                    'inactive_sensors'           => $sensorCounts['inactive'] ?? 0,
                ];
            },
            'SummaryController@index'
        );

        return response()->json($summary);
    }

}
