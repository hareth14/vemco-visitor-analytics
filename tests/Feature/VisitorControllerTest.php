<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Visitor;
use App\Models\Sensor;
use App\Models\Location;
use Carbon\Carbon;

class VisitorControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_visitors_when_no_date_is_provided()
    {
        $location = Location::factory()->create();
        $sensor = Sensor::factory()->create(['location_id' => $location->id]);

        $dates = [
            now()->subDays(1)->toDateString(), 
            now()->subDays(3)->toDateString(), 
            now()->subDays(5)->toDateString()  
        ]; 

        foreach ($dates as $date) {
            Visitor::updateOrCreate(
                [
                    'location_id' => $location->id,
                    'sensor_id' => $sensor->id,
                    'date' => $date
                ],
                ['count' => rand(1, 1000)]
            );
        }

        $response = $this->getJson('/api/visitors');

        $response->assertStatus(200);

        // Assert that all visitors are returned (3)
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function index_returns_visitors_filtered_by_date()
    {
        $location = Location::factory()->create();
        $sensor = Sensor::factory()->create(['location_id' => $location->id]);

        // Create visitors for two different dates
        Visitor::factory()->create([
            'location_id' => $location->id,
            'sensor_id' => $sensor->id,
            'date' => '2025-06-10',
        ]);

        Visitor::factory()->create([
            'location_id' => $location->id,
            'sensor_id' => $sensor->id,
            'date' => '2025-06-11',
        ]);

        // Request visitors for 2025-06-10 only
        $response = $this->getJson('/api/visitors?date=2025-06-10');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('2025-06-10', $data[0]['date']);
    }

    /** @test */
    public function store_creates_a_new_visitor()
    {
        $location = Location::factory()->create();
        $sensor = Sensor::factory()->create(['location_id' => $location->id]);

        $payload = [
            'location_id' => $location->id,
            'sensor_id' => $sensor->id,
            'date' => '2025-06-15',
            'count' => 7,
        ];

        $response = $this->postJson('/api/visitors', $payload);

        $response->assertStatus(201);

        $response->assertJson([
            'data' => [
                'location_id' => $payload['location_id'],
                'sensor_id' => $payload['sensor_id'],
                'date' => $payload['date'],
                'count' => $payload['count'],
            ],
        ]);

        // Assert in database
        $this->assertDatabaseHas('visitors', $payload);
    }

    /** @test */
    public function store_validates_input_data()
    {
        $response = $this->postJson('/api/visitors', []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['location_id', 'sensor_id', 'date', 'count']);
    }

    /** @test */
    public function store_fails_if_sensor_does_not_belong_to_location()
    {
        $location1 = Location::factory()->create();
        $location2 = Location::factory()->create();

        $sensor = Sensor::factory()->create(['location_id' => $location2->id]);

        $payload = [
            'location_id' => $location1->id,
            'sensor_id' => $sensor->id,
            'date' => '2025-06-15',
            'count' => 5,
        ];

        $response = $this->postJson('/api/visitors', $payload);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors('sensor_id');
    }
}
