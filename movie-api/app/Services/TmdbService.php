<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.themoviedb.org/3';
        $this->apiKey = config('services.tmdb.api_key');
    }

    public function getMovies($category = 'popular', $page = 1)
    {
        $cacheKey = "movies_{$category}_{$page}";

        return Cache::remember($cacheKey, 3600, function () use ($category, $page) {
            try {
                $response = Http::get("{$this->baseUrl}/movie/{$category}", [
                    'api_key' => $this->apiKey,
                    'page' => $page,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('TMDB API Error', [
                    'category' => $category,
                    'page' => $page,
                    'status' => $response->status(),
                ]);

                return ['results' => [], 'total_pages' => 0];
            } catch (\Exception $e) {
                Log::error('TMDB Service Exception', [
                    'message' => $e->getMessage(),
                    'category' => $category,
                ]);

                return ['results' => [], 'total_pages' => 0];
            }
        });
    }

    public function getMovieDetails($movieId)
    {
        $cacheKey = "movie_details_{$movieId}";

        return Cache::remember($cacheKey, 7200, function () use ($movieId) {
            try {
                $response = Http::get("{$this->baseUrl}/movie/{$movieId}", [
                    'api_key' => $this->apiKey,
                    'append_to_response' => 'videos,credits',
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                throw new \Exception('Failed to fetch movie details');
            } catch (\Exception $e) {
                Log::error('Movie details fetch failed', [
                    'movie_id' => $movieId,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function searchMovies($query, $page = 1)
    {
        $cacheKey = "search_" . md5($query) . "_$page";

        return Cache::remember($cacheKey, 1800, function () use ($query, $page) {
            try {
                $response = Http::get("{$this->baseUrl}/search/movie", [
                    'api_key' => $this->apiKey,
                    'query' => $query,
                    'page' => $page,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['results' => [], 'total_pages' => 0];
            } catch (\Exception $e) {
                Log::error('Movie search failed', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                ]);

                return ['results' => [], 'total_pages' => 0];
            }
        });
    }
}