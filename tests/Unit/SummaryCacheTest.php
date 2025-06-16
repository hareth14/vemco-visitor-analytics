<?php
namespace Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SummaryCacheTest extends TestCase
{
    /** @test */
    public function it_returns_cached_data_if_exists()
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
