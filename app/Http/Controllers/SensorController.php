<?php
namespace App\Http\Controllers;

use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\CacheHelper;

class SensorController extends Controller
{

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $page = $request->query('page', 1);
        $cacheKey = "sensors_{$status}_page_{$page}";

        // Use CacheHelper to handle caching with fallback
        // If the status is 'all', we don't filter by status
        $sensors = CacheHelper::rememberWithFallback(
            $cacheKey,
            ['sensors'],
            10, // Cache duration in minutes
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

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'name' => [
                'required',
                'string',
                Rule::unique('sensors')->where(fn ($query) =>
                    $query->where('location_id', $request->location_id)
                ),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $sensor = Sensor::create([
            'name' => $request->name,
            'status' => $request->status,
            'location_id' => $request->location_id,
        ]);

        try {
            // Clear the cache for sensors after creation
            Cache::tags(['sensors'])->flush();
        } catch (\Exception $e) {
            Log::channel('redis')->error('Redis cache failed in SensorController@store', [
                'message' => $e->getMessage(),
            ]);
        }

        return new SensorResource($sensor);
    }
}
