<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Location;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_locations()
    {
        $locations = Location::factory()->count(5)->create();

        $response = $this->getJson('/api/locations');

        $response->assertStatus(200);

        $response->assertJsonCount(5, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    /** @test */
    public function store_creates_location_with_valid_data()
    {
        $payload = [
            'name' => 'New Location Name',
        ];

        $response = $this->postJson('/api/locations', $payload);

        $response->assertStatus(201);

        $response->assertJsonFragment(['name' => $payload['name']]);

        $this->assertDatabaseHas('locations', [
            'name' => $payload['name'],
        ]);
    }

    /** @test */
    public function store_fails_validation_without_name()
    {
        $response = $this->postJson('/api/locations', []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function store_fails_if_name_is_not_unique()
    {
        $existing = Location::factory()->create(['name' => 'Unique Location']);

        $response = $this->postJson('/api/locations', ['name' => 'Unique Location']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }
}
