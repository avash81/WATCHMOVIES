<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Movie;

class ImportTMDBMovies extends Command
{
    protected $signature = 'movies:import {--page=1}';
    protected $description = 'Import popular movies from TMDB API';

    public function handle()
    {
        $apiKey = env('TMDB_API_KEY');
        $page = $this->option('page');

        if (!$apiKey) {
            $this->error('TMDB_API_KEY not found in .env file!');
            return;
        }

        $url = "https://api.themoviedb.org/3/movie/popular";
        $response = Http::get($url, [
            'api_key' => $apiKey,
            'page' => $page,
        ]);

        if (!$response->successful()) {
            $this->error('Failed to fetch movies from TMDB API.');
            return;
        }

        $movies = $response->json('results');

        foreach ($movies as $m) {
            Movie::updateOrCreate(
                ['title' => $m['title']],
                [
                    'description' => $m['overview'] ?? 'No description available.',
                    'release_year' => isset($m['release_date']) ? substr($m['release_date'], 0, 4) : null,
                    'genre' => 'Hollywood',
                    'poster_url' => $m['poster_path']
                        ? 'https://image.tmdb.org/t/p/w500' . $m['poster_path']
                        : null,
                    'category' => 'Hollywood',
                ]
            );
        }

        $this->info(count($movies) . ' movies imported successfully!');
    }
}
