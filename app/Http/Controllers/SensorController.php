<?php
namespace App\Http\Controllers;

use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class SensorController extends Controller
{

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $page = $request->query('page', 1);
        $cacheKey = "sensors_{$status}_page_{$page}";

        try {
            $sensors = Cache::tags(['sensors'])->remember($cacheKey, now()->addMinutes(10), function () use ($status) {
                $query = Sensor::with('location');

                if (in_array($status, ['active', 'inactive'])) {
                    $query->where('status', $status);
                }

                return $query->paginate(10);
            });
        } catch (\Exception $e) {
            // If Redis fails, fall back to the database
            Log::channel('redis')->error('Redis cache failed in SensorController@index', [
                'message' => $e->getMessage(),
                'status' => $status,
                'page' => $page,
            ]);

            $query = Sensor::with('location');
            if (in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            }

            $sensors = $query->paginate(10);
        }

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
