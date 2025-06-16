<?php
namespace App\Http\Controllers;

use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\CacheHelper;
use App\Http\Requests\StoreSensorRequest;

class SensorController extends Controller
{
    // GET /api/sensors
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $page = $request->query('page', 1);
        $cacheKey = "sensors_{$status}_page_{$page}";

        // Use CacheHelper to handle caching with fallback and dynamic TTL
        // If the status is 'all', we don't filter by status
        $sensors = CacheHelper::rememberWithFallback(
            $cacheKey,
            ['sensors'],
            'high_freq', // High frequency for changing sensor list
            function () use ($status) {
                $query = Sensor::with('location');

                if (in_array($status, ['active', 'inactive'])) {
                    $query->where('status', $status);
                }

                return $query->paginate(10);
            },
            'SensorController@index'
        );

        return SensorResource::collection($sensors);
    }

    // POST /api/sensors
    public function store(StoreSensorRequest $request)
    {
        // Validate the request using the StoreSensorRequest
        $sensor = Sensor::create($request->validated());

        // Flush the cache for sensors after creating a new sensor with specific tags
        CacheHelper::flushWithFallback(['sensors'], 'SensorController@store');

        // Flush the summary cache if it exists
        CacheHelper::forgetWithFallback('summary_dashboard', 'SensorController@store');
        
        return new SensorResource($sensor);
    }
}
