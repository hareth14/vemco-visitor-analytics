<?php

namespace App\Http\Controllers;

use App\Http\Resources\VisitorResource;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Rules\SensorBelongsToLocation;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date');

        // Validate date format if provided
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([
                'message' => 'The date format must be YYYY-MM-DD.',
            ], 422);
        }

        if ($date) {
            $cacheKey = "visitors_date_{$date}";

            try {
                // Cache only if a specific date is provided, as per task instructions.
                $visitors = Cache::tags(['visitors'])->remember($cacheKey, now()->addMinutes(10), function () use ($date) {
                    return Visitor::with(['location', 'sensor'])
                        ->whereDate('date', $date)
                        ->get();
                });
            } catch (\Exception $e) {
                Log::channel('redis')->error('Redis cache failed in VisitorController@index', [
                    'message' => $e->getMessage(),
                    'date' => $date,
                ]);

                $visitors = Visitor::with(['location', 'sensor'])
                    ->whereDate('date', $date)
                    ->get();
            }
        } else {
            // No date provided, fetch all without caching
            $visitors = Visitor::with(['location', 'sensor'])->get();
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

        try {
            // Clear the cache for visitors after creation
            Cache::tags(['visitors'])->flush();
        } catch (\Exception $e) {
            Log::channel('redis')->error('Redis cache failed in VisitorController@store', [
                'message' => $e->getMessage(),
            ]);
        }

        return new VisitorResource($visitor->load(['location', 'sensor']));
    }
}
