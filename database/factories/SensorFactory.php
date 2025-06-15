<?php

namespace Database\Factories;

use App\Models\Sensor;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class SensorFactory extends Factory
{
    protected $model = Sensor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word . '-' . $this->faker->unique()->numerify('###'),
            'status' => 'active',
            'location_id' => Location::factory(),
        ];
    }

    public function inactive()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function active()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }
}
