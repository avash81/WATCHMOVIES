<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        $category = $request->get('category', 'popular');
        $page = $request->get('page', 1);

        $data = $this->tmdbService->getMovies($category, $page);

        return response()->json([
            'success' => true,
            'data' => $data['results'],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $data['total_pages'],
            ],
            'performance' => [
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Check if it's a TMDB ID (numeric) or our database ID
            if (is_numeric($id)) {
                $movie = $this->tmdbService->getMovieDetails($id);
            } else {
                // If it's not numeric, try to find by database ID
                $movieModel = Movie::find($id);
                if (!$movieModel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Movie not found'
                    ], 404);
                }
                $movie = $this->tmdbService->getMovieDetails($movieModel->tmdb_id);
            }

            return response()->json([
                'success' => true,
                'data' => $movie,
                'performance' => [
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Movie details error', [
                'movie_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Movie not found or service unavailable'
            ], 404);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        $query = $request->get('query');
        $page = $request->get('page', 1);

        if (!$query || strlen(trim($query)) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters long'
            ], 400);
        }

        $data = $this->tmdbService->searchMovies(trim($query), $page);

        return response()->json([
            'success' => true,
            'data' => $data['results'],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $data['total_pages'],
            ],
            'performance' => [
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]
        ]);
    }

    public function trending(): JsonResponse
    {
        $startTime = microtime(true);
        
        try {
            $data = $this->tmdbService->getTrendingMovies(10);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'performance' => [
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Trending movies error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trending movies'
            ], 500);
        }
    }

    public function quickSearch(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        
        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $movies = Movie::where('title', 'like', "{$query}%")
                      ->select(['id', 'tmdb_id', 'title', 'poster_path', 'release_date'])
                      ->orderBy('popularity', 'desc')
                      ->take(5)
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $movies
        ]);
    }

    public function enhancedDetails($id): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $movie = $this->tmdbService->getEnhancedMovieDetails($id);

            return response()->json([
                'success' => true,
                'data' => $movie,
                'performance' => [
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Enhanced details error', ['movie_id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Enhanced details not available'
            ], 404);
        }
    }

    public function genres(): JsonResponse
    {
        try {
            $genres = $this->tmdbService->getGenres();

            return response()->json([
                'success' => true,
                'data' => $genres
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch genres'
            ], 500);
        }
    }

    public function moviesByGenre($genreId): JsonResponse
    {
        try {
            $page = request()->get('page', 1);
            $data = $this->tmdbService->getMoviesByGenre($genreId, $page);

            return response()->json([
                'success' => true,
                'data' => $data['results'],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $data['total_pages'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch genre movies'
            ], 500);
        }
    }
}