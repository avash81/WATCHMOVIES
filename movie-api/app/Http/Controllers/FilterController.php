<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilterController extends Controller
{
    public function filterMovies(Request $request): JsonResponse
    {
        Log::info('Filter request received', $request->all());
        
        try {
            // Start with base query
            $query = Movie::query();
            
            // Genre filter - UNIVERSAL APPROACH
            if ($request->has('genre') && $request->genre) {
                $genreId = (int)$request->genre;
                
                // Get all movies first, then filter by genre in PHP
                // This is less efficient but works with all databases
                $movieIdsWithGenre = Movie::all()
                    ->filter(function ($movie) use ($genreId) {
                        $genreIds = $movie->genre_ids;
                        if (is_string($genreIds)) {
                            $genreIds = json_decode($genreIds, true) ?? [];
                        }
                        return in_array($genreId, (array)$genreIds);
                    })
                    ->pluck('id');
                
                $query->whereIn('id', $movieIdsWithGenre);
                Log::info("Applied genre filter: {$genreId}, found {$movieIdsWithGenre->count()} movies");
            }
            
            // Year range filter
            if ($request->has('year_from') && $request->year_from) {
                $yearFrom = $request->year_from;
                $query->whereYear('release_date', '>=', $yearFrom);
            }
            
            if ($request->has('year_to') && $request->year_to) {
                $yearTo = $request->year_to;
                $query->whereYear('release_date', '<=', $yearTo);
            }
            
            // Rating filter
            if ($request->has('min_rating') && $request->min_rating) {
                $minRating = (float)$request->min_rating;
                $query->where('vote_average', '>=', $minRating);
            }
            
            if ($request->has('max_rating') && $request->max_rating) {
                $maxRating = (float)$request->max_rating;
                $query->where('vote_average', '<=', $maxRating);
            }
            
            // Language filter
            if ($request->has('language') && $request->language) {
                $language = $request->language;
                $query->where('original_language', $language);
            }
            
            // Search query
            if ($request->has('query') && $request->query) {
                $searchQuery = $request->query;
                $query->where(function($q) use ($searchQuery) {
                    $q->where('title', 'like', "%{$searchQuery}%")
                      ->orWhere('overview', 'like', "%{$searchQuery}%");
                });
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'popularity');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $validSortFields = ['popularity', 'vote_average', 'release_date', 'title'];
            if (in_array($sortBy, $validSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            // Pagination
            $perPage = min($request->get('per_page', 20), 50);
            $page = $request->get('page', 1);
            
            $movies = $query->paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'data' => $movies->items(),
                'pagination' => [
                    'current_page' => $movies->currentPage(),
                    'total_pages' => $movies->lastPage(),
                    'total_movies' => $movies->total(),
                    'per_page' => $movies->perPage()
                ],
                'filters_applied' => $request->all()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Filter error', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Filtering failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    public function getFilterOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'filters' => [
                'genres' => $this->getAvailableGenres(),
                'years' => $this->getAvailableYears(),
                'languages' => $this->getAvailableLanguages(),
                'sort_options' => [
                    'popularity' => 'Most Popular',
                    'vote_average' => 'Highest Rated', 
                    'release_date' => 'Newest First',
                    'title' => 'Alphabetical'
                ]
            ]
        ]);
    }
    
    private function getAvailableGenres()
    {
        return [
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
        ];
    }
    
    private function getAvailableYears()
    {
        $currentYear = date('Y');
        return range($currentYear, 1900);
    }
    
    private function getAvailableLanguages()
    {
        return [
            'en' => 'English',
            'hi' => 'Hindi', 
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese',
        ];
    }
}