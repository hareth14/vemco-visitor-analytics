<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Visitor;
use App\Models\Sensor;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SummaryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_summary_data()
    {
        // Arrange
        $location = Location::factory()->create();

        Sensor::factory()->count(3)->active()->create(['location_id' => $location->id]);
        Sensor::factory()->count(2)->inactive()->create(['location_id' => $location->id]);
        
        $existingSensor = Sensor::where('status', 'active')->first();

        // Visitors in last 7 days (only this counts)
        Visitor::factory()->create([
            'location_id' => $location->id,
            'sensor_id' => $existingSensor->id,
            'date' => Carbon::now()->subDays(3)->toDateString(),
            'count' => 10,
        ]);

        // Visitor outside 7 days window (excluded)
        Visitor::factory()->create([
            'location_id' => $location->id,
            'sensor_id' => $existingSensor->id,
            'date' => Carbon::now()->subDays(10)->toDateString(),
            'count' => 5,
        ]);

        // Act
        $response = $this->getJson('/api/summary');

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'total_visitors_last_7_days' => 10,
            'active_sensors' => 3,
            'inactive_sensors' => 2,
        ]);
    }

    /** @test */
    public function test_summary_cache_returns_cached_data_if_exists()
    {
        $cachedData = [
            'total_visitors_last_7_days' => 99,
            'active_sensors' => 7,
            'inactive_sensors' => 0,
        ];
        
        Cache::shouldReceive('tags')
            ->with(['summary'])
            ->andReturnSelf();

        Cache::shouldReceive('remember')
            ->with('summary_dashboard', \Mockery::any(), \Mockery::type(\Closure::class))
            ->andReturn($cachedData);

        $response = $this->getJson('/api/summary');

        $response->assertStatus(200);
        $response->assertExactJson($cachedData);
    }
}
