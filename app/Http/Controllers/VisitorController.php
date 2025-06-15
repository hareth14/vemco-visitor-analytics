<?php

namespace App\Http\Controllers;

use App\Http\Resources\VisitorResource;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Rules\SensorBelongsToLocation;
use App\Helpers\CacheHelper;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        // Validate date format if provided
        $request->validate([
            'date' => ['date_format:Y-m-d']
        ]);
        $date = $request->query('date');

        // Fetch visitors for a specific date with caching
        // If a date is provided, we cache the results
        if ($date) {
            $cacheKey = "visitors_date_{$date}";

            // Use CacheHelper to handle caching with fallback
            $visitors = CacheHelper::rememberWithFallback(
                $cacheKey,
                ['visitors'],
                10, // Cache duration in minutes
                function () use ($date) {
                    return Visitor::with(['location', 'sensor'])
                        ->whereDate('date', $date)
                        ->get();
                },
                'VisitorController@index'
            );
        } else {
            // No date provided, fetch all without caching
            $visitors = Visitor::with(['location', 'sensor'])->paginate(10);
        }

        return VisitorResource::collection($visitors);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'sensor_id' => ['required', 'exists:sensors,id', new SensorBelongsToLocation($request->location_id)],
            'date'        => ['required', 'date_format:Y-m-d'],
            'count'       => ['required', 'integer', 'min:0'],
        ]);

        $visitor = Visitor::updateOrCreate(
            [
                'location_id' => $data['location_id'],
                'sensor_id'   => $data['sensor_id'],
                'date'        => $data['date'],
            ],
            ['count' => $data['count']]
        );

        // Flush the cache for visitors after storing a new visitor
        // This ensures that the next request will fetch the updated list
        // Use CacheHelper to handle cache flushing with fallback
        CacheHelper::flushWithFallback(['visitors'], 'VisitorController@store');

        return new VisitorResource($visitor->load(['location', 'sensor']));
    }
}
