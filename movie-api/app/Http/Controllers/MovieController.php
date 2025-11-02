<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    public function index(Request $request): JsonResponse
    {
        $category = $request->get('category', 'popular');
        $page = $request->get('page', 1);

        $data = $this->tmdbService->getMovies($category, $page);

        return response()->json([
            'success' => true,
            'data' => $data['results'],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $data['total_pages'],
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        try {
            $movie = $this->tmdbService->getMovieDetails($id);

            return response()->json([
                'success' => true,
                'data' => $movie
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found'
            ], 404);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query');
        $page = $request->get('page', 1);

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $data = $this->tmdbService->searchMovies($query, $page);

        return response()->json([
            'success' => true,
            'data' => $data['results'],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $data['total_pages'],
            ]
        ]);
    }
}