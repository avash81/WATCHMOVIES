<?php

namespace App\Services;

use App\Models\Movie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TmdbService
{
    protected $baseUrl;
    protected $apiKey;

    // Optimized cache durations
    const CACHE_DURATION = [
        'movies' => 900,       // 15 minutes
        'details' => 1800,     // 30 minutes
        'enhanced' => 3600,    // 1 hour
        'genres' => 43200,     // 12 hours
        'search' => 600,       // 10 minutes
        'trending' => 1800,    // 30 minutes
    ];

    public function __construct()
    {
        $this->baseUrl = 'https://api.themoviedb.org/3';
        $this->apiKey = env('TMDB_API_KEY');
        
        if (empty($this->apiKey)) {
            Log::error('TMDB API key is not set in environment variables');
        }
    }

    /**
     * ULTRA-FAST: Get movie details - database first, API fallback
     */
    public function getMovieDetails($movieId)
    {
        $cacheKey = "movie_details_{$movieId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION['details'], function () use ($movieId) {
            try {
                // 1. First try database (fastest - under 10ms)
                $movie = Movie::where('tmdb_id', $movieId)
                    ->select([
                        'id', 'tmdb_id', 'title', 'overview', 'poster_path', 'backdrop_path',
                        'release_date', 'vote_average', 'vote_count', 'popularity',
                        'genre_ids', 'original_language', 'original_title', 'adult', 'video'
                    ])
                    ->first();

                if ($movie && !empty($movie->overview)) {
                    Log::info("✅ Database hit for movie: {$movie->title}");
                    return [
                        'id' => $movie->tmdb_id,
                        'title' => $movie->title,
                        'overview' => $movie->overview,
                        'poster_path' => $movie->poster_path,
                        'backdrop_path' => $movie->backdrop_path,
                        'release_date' => $movie->release_date,
                        'vote_average' => $movie->vote_average,
                        'vote_count' => $movie->vote_count,
                        'popularity' => $movie->popularity,
                        'genres' => $this->convertGenreIdsToNames($movie->genre_ids),
                        'original_language' => $movie->original_language,
                        'original_title' => $movie->original_title,
                        'adult' => $movie->adult,
                        'video' => $movie->video,
                        'source' => 'database'
                    ];
                }

                // 2. Fallback to TMDB API (slower - 200-500ms)
                Log::info("🔄 Fetching from TMDB API: {$movieId}");
                $response = Http::timeout(6)->retry(2, 100)->get("{$this->baseUrl}/movie/{$movieId}", [
                    'api_key' => $this->apiKey,
                    'language' => 'en-US'
                ]);

                if ($response->successful()) {
                    $movieData = $response->json();
                    // Store in background to not block response
                    $this->storeMovieDetailsBackground($movieData);
                    return $movieData;
                }

                throw new \Exception('TMDB API failed - Status: ' . $response->status());

            } catch (\Exception $e) {
                Log::warning('Movie details fetch failed, using basic data', [
                    'movie_id' => $movieId,
                    'error' => $e->getMessage()
                ]);
                
                // 3. Final fallback - basic movie data
                return $this->getBasicMovieData($movieId);
            }
        });
    }

    /**
     * Get basic movie data as fallback
     */
    protected function getBasicMovieData($movieId)
    {
        $movie = Movie::where('tmdb_id', $movieId)
            ->select(['id', 'tmdb_id', 'title', 'poster_path', 'release_date', 'vote_average'])
            ->first();

        if ($movie) {
            return [
                'id' => $movie->tmdb_id,
                'title' => $movie->title,
                'poster_path' => $movie->poster_path,
                'release_date' => $movie->release_date,
                'vote_average' => $movie->vote_average,
                'overview' => 'Description not available at the moment.',
                'vote_count' => 0,
                'popularity' => 0,
                'genres' => [],
                'source' => 'database_basic'
            ];
        }

        return [
            'id' => $movieId,
            'title' => 'Movie Not Found',
            'overview' => 'This movie is not available in our database.',
            'poster_path' => null,
            'source' => 'not_found'
        ];
    }

    /**
     * Convert genre IDs to names
     */
    protected function convertGenreIdsToNames($genreIds)
    {
        if (empty($genreIds)) return [];

        $genreMap = [
            28 => 'Action', 12 => 'Adventure', 16 => 'Animation', 35 => 'Comedy',
            80 => 'Crime', 18 => 'Drama', 10751 => 'Family', 14 => 'Fantasy',
            36 => 'History', 27 => 'Horror', 10402 => 'Music', 9648 => 'Mystery',
            10749 => 'Romance', 878 => 'Science Fiction', 10770 => 'TV Movie',
            53 => 'Thriller', 10752 => 'War', 37 => 'Western'
        ];

        $genres = [];
        foreach ((array)$genreIds as $genreId) {
            if (isset($genreMap[$genreId])) {
                $genres[] = ['id' => $genreId, 'name' => $genreMap[$genreId]];
            }
        }

        return $genres;
    }

    /**
     * Store movie details in background
     */
    protected function storeMovieDetailsBackground($movieData)
    {
        // Use Laravel's dispatch for background processing
        dispatch(function () use ($movieData) {
            try {
                $genreIds = [];
                if (isset($movieData['genres']) && is_array($movieData['genres'])) {
                    $genreIds = array_column($movieData['genres'], 'id');
                }

                Movie::updateOrCreate(
                    ['tmdb_id' => $movieData['id']],
                    [
                        'title' => $movieData['title'] ?? '',
                        'overview' => $movieData['overview'] ?? '',
                        'poster_path' => $movieData['poster_path'] ?? null,
                        'backdrop_path' => $movieData['backdrop_path'] ?? null,
                        'release_date' => $movieData['release_date'] ?? null,
                        'vote_average' => $movieData['vote_average'] ?? 0,
                        'vote_count' => $movieData['vote_count'] ?? 0,
                        'genre_ids' => $genreIds,
                        'popularity' => $movieData['popularity'] ?? 0,
                        'original_language' => $movieData['original_language'] ?? 'en',
                        'original_title' => $movieData['original_title'] ?? '',
                        'adult' => $movieData['adult'] ?? false,
                        'video' => $movieData['video'] ?? false,
                    ]
                );

                Log::info("✅ Background stored: {$movieData['title']}");
            } catch (\Exception $e) {
                Log::error('❌ Background store failed: ' . $e->getMessage());
            }
        });
    }

    /**
     * ULTRA-FAST: Get movies list - database only
     */
    public function getMovies($category = 'popular', $page = 1)
    {
        $cacheKey = "movies_{$category}_{$page}";

        return Cache::remember($cacheKey, self::CACHE_DURATION['movies'], function () use ($category, $page) {
            // Always use database for maximum speed
            return $this->getMoviesFromDatabase($category, $page);
        });
    }

    /**
     * OPTIMIZED: Database-only movies query
     */
    protected function getMoviesFromDatabase($category, $page)
    {
        $perPage = 20;
        $skip = ($page - 1) * $perPage;

        $query = Movie::query()
            ->select([
                'id', 'tmdb_id', 'title', 'poster_path', 'backdrop_path',
                'release_date', 'vote_average', 'vote_count', 'popularity',
                'genre_ids', 'original_language'
            ]);

        // Optimized category handling
        switch ($category) {
            case 'popular':
                $query->orderBy('popularity', 'desc');
                break;
            case 'top_rated':
                $query->where('vote_count', '>', 10)
                      ->orderBy('vote_average', 'desc');
                break;
            case 'now_playing':
                $query->where('release_date', '>=', now()->subMonths(3))
                      ->orderBy('release_date', 'desc');
                break;
            case 'upcoming':
                $query->where('release_date', '>', now())
                      ->orderBy('release_date', 'asc');
                break;
            default:
                $query->orderBy('popularity', 'desc');
        }

        $movies = $query->skip($skip)
                       ->take($perPage)
                       ->get()
                       ->map(function ($movie) {
                           return [
                               'id' => $movie->tmdb_id,
                               'title' => $movie->title,
                               'poster_path' => $movie->poster_path,
                               'backdrop_path' => $movie->backdrop_path,
                               'release_date' => $movie->release_date,
                               'vote_average' => $movie->vote_average,
                               'vote_count' => $movie->vote_count,
                               'genre_ids' => $movie->genre_ids,
                               'original_language' => $movie->original_language,
                               'source' => 'database'
                           ];
                       });

        $total = Movie::count();

        return [
            'results' => $movies->toArray(),
            'total_pages' => ceil($total / $perPage),
            'page' => $page,
            'source' => 'database'
        ];
    }

    /**
     * Enhanced movie details with cast & trailers
     */
    public function getEnhancedMovieDetails($movieId)
    {
        $cacheKey = "movie_enhanced_{$movieId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION['enhanced'], function () use ($movieId) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/movie/{$movieId}", [
                    'api_key' => $this->apiKey,
                    'append_to_response' => 'videos,credits,similar',
                    'language' => 'en-US'
                ]);

                if ($response->successful()) {
                    $movieData = $response->json();
                    $this->storeEnhancedMovieDetails($movieData);
                    return $movieData;
                }

                throw new \Exception('Failed to fetch enhanced details');
            } catch (\Exception $e) {
                Log::error('Enhanced details failed', ['movie_id' => $movieId, 'error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    protected function storeEnhancedMovieDetails($movieData)
    {
        try {
            $cast = [];
            if (isset($movieData['credits']['cast'])) {
                $cast = array_slice($movieData['credits']['cast'], 0, 10);
            }

            $trailers = [];
            if (isset($movieData['videos']['results'])) {
                $trailers = array_filter($movieData['videos']['results'], function($video) {
                    return $video['type'] === 'Trailer' && $video['site'] === 'YouTube';
                });
                $trailers = array_slice($trailers, 0, 3);
            }

            Movie::where('tmdb_id', $movieData['id'])->update([
                'cast' => json_encode($cast),
                'trailer_url' => !empty($trailers) ? "https://www.youtube.com/watch?v={$trailers[0]['key']}" : null,
                'details' => $movieData
            ]);

            Log::info("Enhanced details stored: {$movieData['title']}");
            
        } catch (\Exception $e) {
            Log::error('Failed to store enhanced details', [
                'movie_id' => $movieData['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Search movies with database fallback
     */
    public function searchMovies($query, $page = 1)
    {
        $cacheKey = "search_" . md5($query) . "_$page";

        return Cache::remember($cacheKey, self::CACHE_DURATION['search'], function () use ($query, $page) {
            try {
                $response = Http::timeout(8)->get("{$this->baseUrl}/search/movie", [
                    'api_key' => $this->apiKey,
                    'query' => $query,
                    'page' => $page,
                    'language' => 'en-US'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->storeMoviesInDatabase($data['results']);
                    return $data;
                }

                return $this->searchMoviesInDatabase($query, $page);
            } catch (\Exception $e) {
                Log::error('Search API error', ['query' => $query, 'error' => $e->getMessage()]);
                return $this->searchMoviesInDatabase($query, $page);
            }
        });
    }

    protected function searchMoviesInDatabase($query, $page)
    {
        $perPage = 20;
        $skip = ($page - 1) * $perPage;

        $movies = Movie::where('title', 'like', "%{$query}%")
                      ->orWhere('overview', 'like', "%{$query}%")
                      ->select(['id', 'tmdb_id', 'title', 'poster_path', 'release_date', 'vote_average'])
                      ->orderBy('popularity', 'desc')
                      ->skip($skip)
                      ->take($perPage)
                      ->get();

        $total = Movie::where('title', 'like', "%{$query}%")
                     ->orWhere('overview', 'like', "%{$query}%")
                     ->count();

        return [
            'results' => $movies->toArray(),
            'total_pages' => ceil($total / $perPage),
            'page' => $page
        ];
    }

    /**
     * Get trending movies
     */
    public function getTrendingMovies($limit = 10)
    {
        $cacheKey = "trending_movies_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_DURATION['trending'], function () use ($limit) {
            try {
                $response = Http::timeout(8)->get("{$this->baseUrl}/trending/movie/week", [
                    'api_key' => $this->apiKey,
                    'language' => 'en-US'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $trendingMovies = array_slice($data['results'], 0, $limit);
                    $this->storeMoviesInDatabase($trendingMovies);
                    return $trendingMovies;
                }

                // Fallback to database
                return Movie::select(['id', 'tmdb_id', 'title', 'poster_path', 'popularity'])
                           ->orderBy('popularity', 'desc')
                           ->take($limit)
                           ->get()
                           ->toArray();
            } catch (\Exception $e) {
                Log::error('Trending movies failed', ['error' => $e->getMessage()]);
                return Movie::select(['id', 'tmdb_id', 'title', 'poster_path'])
                           ->orderBy('popularity', 'desc')
                           ->take($limit)
                           ->get()
                           ->toArray();
            }
        });
    }

    /**
     * Store movies in database
     */
    public function storeMoviesInDatabase($movies)
    {
        if (empty($movies)) {
            Log::warning('No movies to store');
            return 0;
        }

        Log::info('Storing ' . count($movies) . ' movies in database');
        $storedCount = 0;

        foreach ($movies as $movieData) {
            try {
                if (empty($movieData['id']) || empty($movieData['title'])) {
                    continue;
                }

                $genreIds = $movieData['genre_ids'] ?? [];
                if (!is_array($genreIds)) {
                    $genreIds = [];
                }

                $movie = Movie::updateOrCreate(
                    ['tmdb_id' => $movieData['id']],
                    [
                        'title' => $movieData['title'] ?? '',
                        'overview' => $movieData['overview'] ?? '',
                        'poster_path' => $movieData['poster_path'] ?? null,
                        'backdrop_path' => $movieData['backdrop_path'] ?? null,
                        'release_date' => $movieData['release_date'] ?? null,
                        'vote_average' => $movieData['vote_average'] ?? 0,
                        'vote_count' => $movieData['vote_count'] ?? 0,
                        'genre_ids' => $genreIds,
                        'popularity' => $movieData['popularity'] ?? 0,
                        'original_language' => $movieData['original_language'] ?? 'en',
                        'original_title' => $movieData['original_title'] ?? '',
                        'video' => $movieData['video'] ?? false,
                        'adult' => $movieData['adult'] ?? false,
                    ]
                );

                if ($movie->wasRecentlyCreated) {
                    $storedCount++;
                }

            } catch (\Exception $e) {
                Log::error('Failed to store movie', [
                    'tmdb_id' => $movieData['id'] ?? 'unknown',
                    'title' => $movieData['title'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("Storage completed. New: {$storedCount}, Total: " . Movie::count());
        return $storedCount;
    }

    /**
     * Get genres
     */
    public function getGenres()
    {
        $cacheKey = "movie_genres";

        return Cache::remember($cacheKey, self::CACHE_DURATION['genres'], function () {
            try {
                $response = Http::timeout(8)->get("{$this->baseUrl}/genre/movie/list", [
                    'api_key' => $this->apiKey,
                    'language' => 'en-US'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch genres', ['error' => $e->getMessage()]);
            }

            // Fallback genres
            return [
                'genres' => [
                    ['id' => 28, 'name' => 'Action'],
                    ['id' => 12, 'name' => 'Adventure'],
                    ['id' => 16, 'name' => 'Animation'],
                    ['id' => 35, 'name' => 'Comedy'],
                    ['id' => 80, 'name' => 'Crime'],
                    ['id' => 18, 'name' => 'Drama'],
                    ['id' => 10751, 'name' => 'Family'],
                    ['id' => 14, 'name' => 'Fantasy'],
                    ['id' => 36, 'name' => 'History'],
                    ['id' => 27, 'name' => 'Horror'],
                    ['id' => 10402, 'name' => 'Music'],
                    ['id' => 9648, 'name' => 'Mystery'],
                    ['id' => 10749, 'name' => 'Romance'],
                    ['id' => 878, 'name' => 'Science Fiction'],
                    ['id' => 10770, 'name' => 'TV Movie'],
                    ['id' => 53, 'name' => 'Thriller'],
                    ['id' => 10752, 'name' => 'War'],
                    ['id' => 37, 'name' => 'Western'],
                ]
            ];
        });
    }

    /**
     * Get movies by genre
     */
    public function getMoviesByGenre($genreId, $page = 1)
    {
        $cacheKey = "movies_genre_{$genreId}_{$page}";

        return Cache::remember($cacheKey, 1800, function () use ($genreId, $page) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/discover/movie", [
                    'api_key' => $this->apiKey,
                    'with_genres' => $genreId,
                    'page' => $page,
                    'language' => 'en-US',
                    'sort_by' => 'popularity.desc'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->storeMoviesInDatabase($data['results']);
                    return $data;
                }

                return $this->getMoviesByGenreFromDatabase($genreId, $page);
            } catch (\Exception $e) {
                Log::error('Genre movies API error', ['genre_id' => $genreId, 'error' => $e->getMessage()]);
                return $this->getMoviesByGenreFromDatabase($genreId, $page);
            }
        });
    }

    protected function getMoviesByGenreFromDatabase($genreId, $page)
    {
        $perPage = 20;
        $skip = ($page - 1) * $perPage;

        $movies = Movie::whereJsonContains('genre_ids', $genreId)
                      ->select(['id', 'tmdb_id', 'title', 'poster_path', 'release_date', 'vote_average'])
                      ->orderBy('popularity', 'desc')
                      ->skip($skip)
                      ->take($perPage)
                      ->get();

        $total = Movie::whereJsonContains('genre_ids', $genreId)->count();

        return [
            'results' => $movies->toArray(),
            'total_pages' => ceil($total / $perPage),
            'page' => $page
        ];
    }

    /**
     * Populate initial movies
     */
    public function populateInitialMovies()
    {
        try {
            Log::info('🎬 Starting initial movie population...');
            
            $response = Http::timeout(15)->get("{$this->baseUrl}/movie/popular", [
                'api_key' => $this->apiKey,
                'page' => 1,
                'language' => 'en-US'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $movies = $data['results'];
                
                Log::info('✅ Fetched ' . count($movies) . ' movies from TMDB');
                
                $storedCount = $this->storeMoviesInDatabase($movies);
                $finalCount = Movie::count();
                
                Log::info("🎉 Storage completed. Stored: {$storedCount}, Total: {$finalCount}");
                
                return [
                    'success' => true,
                    'fetched_count' => count($movies),
                    'stored_count' => $storedCount,
                    'final_count' => $finalCount
                ];
            } else {
                $errorMsg = 'TMDB API error: ' . $response->status();
                Log::error($errorMsg);
                return [
                    'success' => false,
                    'error' => $errorMsg
                ];
            }
        } catch (\Exception $e) {
            $errorMsg = 'Failed to populate movies: ' . $e->getMessage();
            Log::error($errorMsg);
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/movie/550", [
                'api_key' => $this->apiKey
            ]);

            return [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'api_key_set' => !empty($this->apiKey),
                'api_key_length' => strlen($this->apiKey),
                'base_url' => $this->baseUrl
            ];
        } catch (\Exception $e) {
            return [
                'successful' => false,
                'error' => $e->getMessage(),
                'api_key_set' => !empty($this->apiKey),
                'api_key_length' => strlen($this->apiKey)
            ];
        }
    }
}