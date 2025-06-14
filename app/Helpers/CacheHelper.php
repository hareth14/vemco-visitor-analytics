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
     * @param string $cacheKey The key under which to store the cached value.
     * @param array $tags The tags to associate with the cache entry.
     * @param int $minutes The number of minutes to cache the value.
     * @param \Closure $callback The callback function to execute if the cache retrieval fails.
     * @param string $logContext Context for logging errors.
     * @return mixed The cached value or the result of the callback function.
     */
    public static function rememberWithFallback($cacheKey, $tags, $minutes, \Closure $callback, string $logContext)
    {
        try {
            return Cache::tags($tags)->remember($cacheKey, now()->addMinutes($minutes), $callback);
        } catch (\Exception $e) {
            Log::channel('redis')->error("Redis cache failed in $logContext", [
                'message' => $e->getMessage(),
            ]);

            // fallback to original logic (callback)
            return $callback();
        }
    }

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
                    'store' => get_class($store),
                    'context' => $logContext,
                    'tags' => $tags,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('redis')->error("Redis cache flush failed in $logContext", [
                'message' => $e->getMessage(),
            ]);
        }
    }

}
