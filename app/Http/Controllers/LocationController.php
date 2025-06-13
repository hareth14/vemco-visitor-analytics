<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Resources\LocationResource;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // GET /api/locations
    public function index()
    {
        $locations = Location::all();
        return LocationResource::collection($locations);
    }

    // POST /api/locations
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
        ]);

        $location = Location::create($validated);

        return new LocationResource($location);
    }
}
