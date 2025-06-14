<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->setupCacheFallback();
    }

    protected function setupCacheFallback(): void
    {
        // if default cache driver is not redis, no need to check
        if (config('cache.default') !== 'redis') {
            return;
        }

        try {
            Cache::store('redis')->connection()->ping();
        } catch (\Throwable $e) {
            Log::warning("Redis is not available, falling back to file cache", [
                'error' => $e->getMessage()
            ]);

            $this->switchToFileCache();
            $this->verifyFileCacheWorks();
        }
    }

    protected function switchToFileCache(): void
    {
        config(['cache.default' => 'file']);
        
        // Configuring session driver to file if redis is used
        if (config('session.driver') === 'redis') {
            config(['session.driver' => 'file']);
        }
    }

    protected function verifyFileCacheWorks(): void
    {
        try {
            $testKey = 'cache_fallback_test';
            $testValue = now()->toString();
            
            Cache::store('file')->put($testKey, $testValue, 10);
            $retrievedValue = Cache::store('file')->get($testKey);
            
            if ($retrievedValue !== $testValue) {
                Log::error("Cache failed to store or retrieve value correctly", [
                    'expected' => $testValue,
                    'retrieved' => $retrievedValue
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Failed to verify file cache storage", [
                'error' => $e->getMessage()
            ]);
        }
    }
}