<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Sensor;
use App\Models\Visitor;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Locations
        $mallA = Location::create(['name' => 'Mall A']);
        $mallB = Location::create(['name' => 'Mall B']);

        // Sensors
        $sensor1 = Sensor::create([
            'name' => 'Sensor 01',
            'status' => 'active',
            'location_id' => $mallA->id
        ]);

        $sensor2 = Sensor::create([
            'name' => 'Camera 02',
            'status' => 'inactive',
            'location_id' => $mallB->id
        ]);

        // Visitors
        Visitor::create([
            'location_id' => $mallA->id,
            'sensor_id' => $sensor1->id,
            'date' => '2025-05-10',
            'count' => 3
        ]);

        Visitor::create([
            'location_id' => $mallB->id,
            'sensor_id' => $sensor2->id,
            'date' => '2025-05-10',
            'count' => 2
        ]);
    }
}