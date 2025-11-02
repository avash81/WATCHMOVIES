<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $movies = $query->orderBy('created_at', 'desc')->paginate(12);

        // TMDB FALLBACK IF DB EMPTY
        if ($movies->isEmpty()) {
            $movies = $this->fetchFromTMDB($request);
        }

        return response()->json([
            'status' => 'success',
            'data' => $movies->items(),
            'pagination' => [
                'current_page' => $movies->currentPage(),
                'total' => $movies->total(),
                'per_page' => $movies->perPage(),
                'total_pages' => $movies->lastPage()
            ]
        ]);
    }

    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $movie
        ]);
    }

 private function fetchFromTMDB(Request $request)
{
    $key = env('TMDB_API_KEY');
    if (!$key) return collect([]);

    try {
        $endpoint = '/movie/now_playing';
        $params = ['api_key' => $key, 'page' => $request->get('page', 1)];

        if ($request->filled('search')) {
            $endpoint = '/search/movie';
            $params['query'] = $request->search;
        } elseif ($request->filled('category') && $request->category !== 'all') {
            $endpoint = '/discover/movie';
            $params['with_original_language'] = $request->category === 'bollywood' ? 'hi' : 'en';
        }

        $response = Http::timeout(10)->get("https://api.themoviedb.org/3{$endpoint}", $params);

        if (!$response->successful()) {
            Log::error('TMDB API failed', ['status' => $response->status()]);
            return collect([]);
        }

        return collect($response->json('results', []))->map(function ($tmdb) {
            return (object) [
                'id' => $tmdb['id'],
                'tmdb_id' => $tmdb['id'], // Add TMDB ID
                'title' => $tmdb['title'] ?? 'Unknown',
                'poster_url' => $tmdb['poster_path']
                    ? 'https://image.tmdb.org/t/p/w500' . $tmdb['poster_path']
                    : null,
                'release_year' => substr($tmdb['release_date'] ?? '2023', 0, 4),
                'rating' => round($tmdb['vote_average'] ?? 0, 1),
                'category' => str_contains(strtolower($tmdb['title']), 'bollywood') ? 'Bollywood' : 'Hollywood',
            ];
        });
    } catch (\Exception $e) {
        Log::error('TMDB Exception: ' . $e->getMessage());
        return collect([]);
    }
}
}