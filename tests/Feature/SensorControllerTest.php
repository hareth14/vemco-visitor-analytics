<?php
// tests/Feature/SensorControllerTest.php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Sensor;
use App\Models\Location;

class SensorControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_index_returns_paginated_sensor_list()
    {
        $location = Location::factory()->create();
        Sensor::factory()->count(15)->create([
            'location_id' => $location->id,
        ]);
        
        $response = $this->getJson('/api/sensors');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                    'location_id',
                    'location' => [
                        'id',
                        'name',
                    ],
                ],
            ],
            'links',
            'meta',
        ]);

        $this->assertCount(10, $response->json('data'));
    }

    /** @test */
    public function test_store_creates_new_sensor_successfully()
    {
        $location = Location::factory()->create();

        $payload = [
            'name' => 'New Sensor',
            'status' => 'active',
            'location_id' => $location->id,
        ];

        $response = $this->postJson('/api/sensors', $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'New Sensor',
            'status' => 'active',
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('sensors', [
            'name' => 'New Sensor',
            'location_id' => $location->id,
        ]);
    }

    /** @test */
    public function test_store_fails_validation_on_invalid_data()
    {
        $response = $this->postJson('/api/sensors', [
            'name' => '', 
            'status' => 'invalid_status', 
            'location_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'status', 'location_id']);
    }

    /** @test */
    public function test_store_fails_when_name_not_unique_in_same_location()
    {
        $location = Location::factory()->create();

        Sensor::factory()->create([
            'name' => 'Sensor Unique',
            'location_id' => $location->id,
        ]);

        $payload = [
            'name' => 'Sensor Unique',
            'status' => 'active',
            'location_id' => $location->id,
        ];

        $response = $this->postJson('/api/sensors', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }



}
