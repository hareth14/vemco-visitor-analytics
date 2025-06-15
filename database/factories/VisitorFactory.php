<?php

namespace Database\Factories;

use App\Models\Visitor;
use App\Models\Sensor;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;


class VisitorFactory extends Factory
{
    protected $model = Visitor::class;

    
    public function definition(): array
    {
        // Fetch a random location or create a new one if none exist.
        $location = Location::inRandomOrder()->first() ?? Location::factory()->create();

        // Ensure that a sensor exists for the location, either by fetching an existing one or creating a new one.
        $sensor = Sensor::where('location_id', $location->id)->inRandomOrder()->first() ?? 
              Sensor::factory()->create(['location_id' => $location->id]);

        return [
            'location_id' => $location->id,
            'sensor_id' => $sensor->id,
            'date' => now()->subDays(rand(0, 6))->toDateString(),
            'count' => $this->faker->numberBetween(1, 600),
        ];
    }
}