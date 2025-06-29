<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// This helper class provides a method to cache results with a fallback mechanism
// in case the Redis cache fails. It logs the error and executes the callback function to retrieve the data.
class CacheHelper
{
    // NOTE: This fallback ensures the application keeps functioning even if Redis is down.
    // You may switch to hard failure mode if strict Redis usage is required.
    /**
     * Cache a value with a fallback to the callback function if Redis fails.
     *
     * @param string $cacheKey
     * @param array $tags
     * @param string $ttlStrategy
     * @param \Closure $callback
     * @param string $logContext
     * @return mixed
     */
    public static function rememberWithFallback($cacheKey, array $tags, string $ttlStrategy, \Closure $callback, string $logContext)
    {
        $minutes = match ($ttlStrategy) {
            'very_high_freq' => 1,
            'high_freq'      => 5,
            'low_freq'       => 60,
            default          => 10,
        };

        try {
            // Log debug info only in local env
            self::debugCacheCheck($cacheKey, $tags, $logContext);

            return Cache::tags($tags)->remember($cacheKey, now()->addMinutes($minutes), $callback);
        } catch (\Exception $e) {
            Log::channel('redis')->error("Redis cache failed in $logContext", [
                'message' => $e->getMessage(),
            ]);
            return $callback();
        }
    }

    /**
     * Flush the cache with nested tag support.
     *
     * @param array $tags
     * @param string $logContext
     * @return void
     */
    public static function flushWithFallback(array $tags, string $logContext = 'unknown')
    {
        try {
            $store = Cache::getStore();

            if (method_exists($store, 'tags')) {
                // Redis is supported and tags are available
                Cache::tags($tags)->flush();
            } else {
                // File driver (or other drivers without tags): do nothing
                Log::warning("Cache store does not support tags for flushing", [
                    'store'   => get_class($store),
                    'context' => $logContext,
                    'tags'    => $tags,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('redis')->error("Redis cache flush failed in $logContext", [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Forget a specific cache key, used for targeted invalidation.
     *
     * @param string $key
     * @param string $logContext
     */
    public static function forgetWithFallback(string $key, string $logContext = 'unknown', array $tags = [])
    {
        try {
            if (!empty($tags)) {
                Cache::tags($tags)->forget($key);
            } else {
                Cache::forget($key);
            }
        } catch (\Exception $e) {
            Log::channel('redis')->error("Redis forget failed in $logContext", [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public static function debugCacheCheck($cacheKey, array $tags, string $context)
    {
        if (app()->environment('local')) {
            try {
                $has = Cache::tags($tags)->has($cacheKey);
                Log::debug("[$context] Cache check for key [$cacheKey] with tags [" . implode(',', $tags) . "]: " . ($has ? 'HIT' : 'MISS'));
            } catch (\Throwable $e) {
                Log::warning("[$context] Failed to check tagged cache: " . $e->getMessage());
            }
        }
    }

}
