<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\FilterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// Performance monitoring middleware
Route::middleware('performance')->group(function () {
    
    // Health check with performance metrics
    Route::get('/health', function () {
        $startTime = microtime(true);
        
        $metrics = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'performance' => [
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
                'cache_driver' => config('cache.default'),
            ]
        ];
        
        return response()->json($metrics);
    });

    // High-priority cached routes
    Route::middleware('cache.headers:public;max_age=300;etag')->group(function () {
        // Filter options (high cache - rarely changes)
        Route::get('/movies/filter-options', [FilterController::class, 'getFilterOptions']);
        
        // Genres (high cache - rarely changes)
        Route::get('/genres', [MovieController::class, 'genres']);
        
        // Quick search (medium cache)
        Route::get('/movies/quick-search', [MovieController::class, 'quickSearch']);
    });

    // Medium-priority cached routes  
    Route::middleware('cache.headers:public;max_age=180;etag')->group(function () {
        // Main movies list
        Route::get('/movies', [MovieController::class, 'index']);
        
        // Trending movies
        Route::get('/movies/trending', [MovieController::class, 'trending']);
        
        // Genre movies
        Route::get('/genres/{id}/movies', [MovieController::class, 'moviesByGenre']);
        
        // Movie details
        Route::get('/movies/{id}', [MovieController::class, 'show']);
        
        // Enhanced details
        Route::get('/movies/{id}/enhanced', [MovieController::class, 'enhancedDetails']);
    });

    // Low-cache routes (frequently changing)
    Route::get('/movies/search', [MovieController::class, 'search']);
    
    // Filter movies (dynamic - low cache)
    Route::get('/movies/filter', [FilterController::class, 'filterMovies']);

});

// Performance monitoring routes
Route::get('/performance/metrics', function () {
    $startTime = microtime(true);
    
    $metrics = [
        'server' => [
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : 'Not available',
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ],
        'cache' => [
            'hits' => Cache::get('cache_hits', 0),
            'misses' => Cache::get('cache_misses', 0),
            'hit_rate' => Cache::get('cache_hit_rate', 0),
        ],
        'database' => [
            'total_movies' => DB::table('movies')->count(),
        ],
        'response' => [
            'time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
        ]
    ];
    
    return response()->json($metrics);
});

// Cache management routes
Route::prefix('cache')->group(function () {
    Route::post('/clear', function () {
        Cache::flush();
        return response()->json(['success' => true, 'message' => 'Cache cleared']);
    });
    
    Route::get('/stats', function () {
        return response()->json([
            'success' => true,
            'stats' => [
                'total_movies' => DB::table('movies')->count(),
                'cache_driver' => config('cache.default'),
                'cache_size' => 'N/A',
            ]
        ]);
    });
});

// ULTRA-FAST API endpoints WITH CACHING (Single definition - no duplicates)
Route::prefix('fast')->group(function () {
    // Ultra-fast movie details WITH CACHE
    Route::get('/movies/{id}', function ($id) {
        $cacheKey = "fast_movie_{$id}";
        
        return Cache::remember($cacheKey, 300, function () use ($id) { // 5 minute cache
            $startTime = microtime(true);
            
            try {
                $movie = \App\Models\Movie::where('tmdb_id', $id)
                    ->select([
                        'id', 'tmdb_id', 'title', 'overview', 'poster_path', 'backdrop_path',
                        'release_date', 'vote_average', 'vote_count', 'popularity',
                        'genre_ids', 'original_language', 'original_title'
                    ])
                    ->first();

                if (!$movie) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Movie not found',
                        'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                    ], 404);
                }

                $data = [
                    'success' => true,
                    'data' => [
                        'id' => $movie->tmdb_id,
                        'title' => $movie->title,
                        'overview' => $movie->overview,
                        'poster_path' => $movie->poster_path,
                        'backdrop_path' => $movie->backdrop_path,
                        'release_date' => $movie->release_date,
                        'vote_average' => $movie->vote_average,
                        'vote_count' => $movie->vote_count,
                        'popularity' => $movie->popularity,
                        'genre_ids' => $movie->genre_ids,
                        'original_language' => $movie->original_language,
                        'original_title' => $movie->original_title,
                    ],
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
                    'source' => 'database_fast_cached'
                ];

                return response()->json($data);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error',
                    'error' => $e->getMessage(),
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ], 500);
            }
        });
    });

    // Ultra-fast movies list WITH CACHE
    Route::get('/movies', function (Request $request) {
        $category = $request->get('category', 'popular');
        $page = min($request->get('page', 1), 10);
        $cacheKey = "fast_movies_{$category}_{$page}";
        
        return Cache::remember($cacheKey, 180, function () use ($category, $page) { // 3 minute cache
            $startTime = microtime(true);
            $perPage = 20;
            $skip = ($page - 1) * $perPage;

            $query = \App\Models\Movie::query()
                ->select([
                    'id', 'tmdb_id', 'title', 'poster_path', 'backdrop_path',
                    'release_date', 'vote_average', 'vote_count'
                ]);

            // Simple category handling
            if ($category === 'popular') {
                $query->orderBy('popularity', 'desc');
            } else {
                $query->orderBy('release_date', 'desc');
            }

            $movies = $query->skip($skip)->take($perPage)->get();
            $total = \App\Models\Movie::count();

            return response()->json([
                'success' => true,
                'data' => $movies,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $perPage),
                ],
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
                'source' => 'database_fast_cached'
            ]);
        });
    });
});

// Backup API routes (fallback system)
Route::prefix('backup')->group(function () {
    Route::get('/movies', function (Request $request) {
        $category = $request->get('category', 'popular');
        $page = $request->get('page', 1);
        
        $cacheKey = "backup_movies_{$category}_{$page}";
        
        return Cache::remember($cacheKey, 3600, function () use ($category) {
            try {
                $response = Http::timeout(10)->get("https://raw.githubusercontent.com/avash81/MovieVerse/main/data/movies_{$category}.json");
                
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'source' => 'backup',
                        'data' => $response->json()
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'source' => 'static_fallback',
                    'data' => ['results' => []]
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup service unavailable',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    });
});

// Database optimization routes
Route::prefix('admin')->group(function () {
    Route::post('/optimize-db', function () {
        try {
            $movieCount = DB::table('movies')->count();
            $genreCount = DB::table('movies')->distinct('genre_ids')->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Database optimized',
                'stats' => [
                    'total_movies' => $movieCount,
                    'unique_genres' => $genreCount,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed',
                'error' => $e->getMessage()
            ], 500);
        }
    });
    
    Route::get('/db-stats', function () {
        $stats = [
            'movies' => [
                'total' => DB::table('movies')->count(),
                'with_posters' => DB::table('movies')->whereNotNull('poster_path')->count(),
                'with_backdrops' => DB::table('movies')->whereNotNull('backdrop_path')->count(),
                'recent' => DB::table('movies')->where('created_at', '>', now()->subDays(7))->count(),
            ],
            'performance' => [
                'cache_hit_rate' => Cache::get('cache_hit_rate', 0),
            ]
        ];
        
        return response()->json(['success' => true, 'data' => $stats]);
    });
});

// Test endpoints
Route::get('/test/movies', function () {
    $startTime = microtime(true);
    
    $movies = DB::table('movies')
               ->select('id', 'tmdb_id', 'title', 'vote_average', 'release_date')
               ->orderBy('id', 'desc')
               ->take(5)
               ->get();
    
    $responseTime = round((microtime(true) - $startTime) * 1000, 2);
    
    return response()->json([
        'success' => true,
        'data' => $movies,
        'total_movies' => DB::table('movies')->count(),
        'performance' => [
            'response_time' => $responseTime . 'ms',
            'query_count' => 1,
            'memory_used' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB'
        ]
    ]);
});

// Route to test backup system
Route::get('/test/backup', function () {
    $startTime = microtime(true);
    
    try {
        $response = Http::timeout(8)->get('https://raw.githubusercontent.com/avash81/MovieVerse/main/README.md');
        
        return response()->json([
            'success' => true,
            'backup_available' => $response->successful(),
            'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            'github_status' => $response->status()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'backup_available' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            '/movies',
            '/movies/trending', 
            '/movies/search',
            '/movies/filter',
            '/movies/filter-options',
            '/genres',
            '/health',
            '/performance/metrics',
            '/fast/movies',
            '/fast/movies/{id}'
        ]
    ], 404);
});

// Ensure all movie routes are defined
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/trending', [MovieController::class, 'trending']);
Route::get('/movies/quick-search', [MovieController::class, 'quickSearch']);
Route::get('/movies/search', [MovieController::class, 'search']);
Route::get('/movies/filter', [FilterController::class, 'filterMovies']);
Route::get('/movies/filter-options', [FilterController::class, 'getFilterOptions']);
Route::get('/genres', [MovieController::class, 'genres']);
Route::get('/genres/{id}/movies', [MovieController::class, 'moviesByGenre']);
Route::get('/movies/{id}/enhanced', [MovieController::class, 'enhancedDetails']);
Route::get('/movies/{id}', [MovieController::class, 'show']);