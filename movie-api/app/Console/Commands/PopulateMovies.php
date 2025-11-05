<?php

namespace App\Console\Commands;

use App\Services\TmdbService;
use Illuminate\Console\Command;

class PopulateMovies extends Command
{
    protected $signature = 'movies:populate';
    protected $description = 'Populate database with initial movies from TMDB';

    public function handle(TmdbService $tmdbService)
    {
        $this->info('🎬 Starting movie population...');
        
        // Test connection first
        $this->info('🔍 Testing TMDB API connection...');
        $connectionTest = $tmdbService->testConnection();
        
        if (isset($connectionTest['error'])) {
            $this->error('❌ Connection test failed: ' . $connectionTest['error']);
            return 1;
        }
        
        $this->info('✅ TMDB API connected successfully!');
        $this->info('📡 API Key: ' . ($connectionTest['api_key_set'] ? 'Set (' . $connectionTest['api_key_length'] . ' chars)' : 'Not set'));
        $this->info('🌐 Status: ' . $connectionTest['status']);
        
        // Now populate movies
        $this->info('💾 Populating database with movies...');
        
        $result = $tmdbService->populateInitialMovies();
        
        if ($result['success']) {
            $this->info('✅ Successfully populated database!');
            $this->info("📥 Fetched: {$result['fetched_count']} movies");
            $this->info("💿 Stored: {$result['stored_count']} movies"); 
            $this->info("📊 Total in database: {$result['final_count']} movies");
            
            // Show sample movies
            $movies = \App\Models\Movie::take(3)->get();
            $this->info("🎭 Sample movies:");
            foreach ($movies as $movie) {
                $this->info("   - {$movie->title} (ID: {$movie->tmdb_id})");
            }
        } else {
            $this->error('❌ Failed to populate movies: ' . $result['error']);
            return 1;
        }
        
        return 0;
    }
}