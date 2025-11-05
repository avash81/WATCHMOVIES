<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Generate cache key for this specific request
        $cacheKey = $this->generateCacheKey($request);
        $wasCached = Cache::has($cacheKey);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $responseTime = round(($endTime - $startTime) * 1000, 2);
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);
        
        // Track cache usage - ONLY if it's a GET request and successful
        if ($request->isMethod('get') && $response->getStatusCode() === 200) {
            if ($wasCached) {
                $this->incrementCacheHit();
                Log::info('Cache HIT', ['key' => $cacheKey, 'url' => $request->url()]);
            } else {
                $this->incrementCacheMiss();
                // Store this response in cache for next time
                Cache::put($cacheKey, [
                    'content' => $response->getContent(),
                    'headers' => $response->headers->all()
                ], 300); // Cache for 5 minutes
                Log::info('Cache MISS - stored', ['key' => $cacheKey, 'url' => $request->url()]);
            }
        }
        
        // If response was cached, return cached response
        if ($wasCached) {
            $cached = Cache::get($cacheKey);
            $response = response($cached['content']);
            foreach ($cached['headers'] as $key => $values) {
                $response->header($key, $values[0]);
            }
        }
        
        // Add performance headers
        $response->headers->set('X-Response-Time', $responseTime . 'ms');
        $response->headers->set('X-Memory-Used', $memoryUsed . 'MB');
        $response->headers->set('X-Cache-Hit', $wasCached ? 'true' : 'false');
        $response->headers->set('X-Cache-Key', $cacheKey);
        
        return $response;
    }
    
    private function generateCacheKey($request)
    {
        // Simple but effective cache key
        return 'api_cache_' . md5($request->fullUrl());
    }
    
    private function incrementCacheHit()
    {
        $hits = Cache::get('cache_hits', 0) + 1;
        Cache::put('cache_hits', $hits, 86400); // 24 hours
        $this->updateHitRate();
        Log::info('Cache hit incremented', ['hits' => $hits]);
    }
    
    private function incrementCacheMiss()
    {
        $misses = Cache::get('cache_misses', 0) + 1;
        Cache::put('cache_misses', $misses, 86400); // 24 hours
        $this->updateHitRate();
        Log::info('Cache miss incremented', ['misses' => $misses]);
    }
    
    private function updateHitRate()
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        $total = $hits + $misses;
        
        if ($total > 0) {
            $hitRate = round(($hits / $total) * 100, 2);
            Cache::put('cache_hit_rate', $hitRate, 86400);
            Log::info('Cache hit rate updated', ['rate' => $hitRate . '%', 'hits' => $hits, 'misses' => $misses]);
        }
    }
}