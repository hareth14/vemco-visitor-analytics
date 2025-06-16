<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Resources\LocationResource;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLocationRequest;

class LocationController extends Controller
{
    // GET /api/locations
    public function index()
    {
        $locations = Location::all();
        return LocationResource::collection($locations);
    }

    // POST /api/locations
    public function store(StoreLocationRequest $request)
    {
        $location = Location::create($request->validated());

        return new LocationResource($location);
    }
}
