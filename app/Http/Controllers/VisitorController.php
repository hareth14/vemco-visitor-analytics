<?php

namespace App\Http\Controllers;

use App\Http\Resources\VisitorResource;
use App\Models\Visitor;
use Illuminate\Http\Request;
use App\Helpers\CacheHelper;
use App\Http\Requests\StoreVisitorRequest;

class VisitorController extends Controller
{
    // GET /api/visitors
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
            $cacheKey = "visitors:date:" . $date;
            // Use CacheHelper to handle caching with fallback
            $visitors = CacheHelper::rememberWithFallback(
                $cacheKey,
                ['visitors'],
                'low_freq',
                function () use ($date) {
                    return Visitor::with(['location', 'sensor'])
                        ->whereDate('date', $date)
                        ->get();
                },
                'VisitorController@index'
            );
        } else {
            // If no date is provided, fetch all visitors with pagination
            // Use a different cache key for all visitors
            $cacheKey = "visitors_all";

            $visitors = CacheHelper::rememberWithFallback(
                $cacheKey,
                ['visitors'],
                'low_freq',
                function () {
                    return Visitor::with(['location', 'sensor'])->paginate(10);
                },
                'VisitorController@index_all'
            );
        }

        return VisitorResource::collection($visitors);
    }

    // POST /api/visitors
    public function store(StoreVisitorRequest $request)
    {
        $data = $request->validated();

        $visitor = Visitor::updateOrCreate(
            [
                'location_id' => $data['location_id'],
                'sensor_id'   => $data['sensor_id'],
                'date'        => $data['date'],
            ],
            ['count' => $data['count']]
        );

        // Flush the cache for visitors of the specific date
        $dateCacheKey = "visitors:date:" . $data['date'];
        CacheHelper::forgetWithFallback($dateCacheKey, 'VisitorController@store', ['visitors']);

        // Flush the cache for all visitors
        CacheHelper::forgetWithFallback("visitors_all", 'VisitorController@store');

        // Flush the summary cache if it exists
        CacheHelper::forgetWithFallback('summary_dashboard', 'VisitorController@store', ['summary']);

        return new VisitorResource($visitor->load(['location', 'sensor']));
    }

}
