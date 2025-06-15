<?php
namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\Sensor;
use Carbon\Carbon;
use App\Helpers\CacheHelper;

class SummaryController extends Controller
{ 
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
                $activeSensors = Sensor::where('status', 'active')->count();
                $inactiveSensors = Sensor::where('status', 'inactive')->count();

                return [
                    'total_visitors_last_7_days' => (int)$totalVisitors,
                    'active_sensors' => $activeSensors,
                    'inactive_sensors' => $inactiveSensors,
                ];
            },
            'SummaryController@index'
        );

        return response()->json($summary);
    }

}
