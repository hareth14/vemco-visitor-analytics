<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Visitor;
use App\Models\Sensor;
use Carbon\Carbon;
use App\Helpers\CacheHelper;

class SummaryController extends Controller
{
    public function index()
    {
        try {
            $summary = Cache::tags(['summary'])->remember('summary_dashboard', now()->addMinutes(1), function () {
                $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

                $totalVisitors = Visitor::where('date', '>=', $sevenDaysAgo)->sum('count');
                $activeSensors = Sensor::where('status', 'active')->count();
                $inactiveSensors = Sensor::where('status', 'inactive')->count();

                return [
                    'total_visitors_last_7_days' => (int)$totalVisitors,
                    'active_sensors' => $activeSensors,
                    'inactive_sensors' => $inactiveSensors,
                ];
            });
        } catch (\Exception $e) {
            Log::channel('redis')->error('Redis cache failed in SummaryController@index', [
                'message' => $e->getMessage(),
            ]);

            $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
            $totalVisitors = Visitor::where('date', '>=', $sevenDaysAgo)->sum('count');
            $activeSensors = Sensor::where('status', 'active')->count();
            $inactiveSensors = Sensor::where('status', 'inactive')->count();

            $summary = [
                'total_visitors_last_7_days' => (int)$totalVisitors,
                'active_sensors' => $activeSensors,
                'inactive_sensors' => $inactiveSensors,
            ];
        }

        return response()->json($summary);
    }
}