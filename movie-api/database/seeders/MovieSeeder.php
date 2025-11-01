<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Check if movies already exist to prevent duplicates during testing
        if (Movie::count() === 0) {
            Movie::create([
                'title' => 'The Great Escape',
                'description' => 'Allied prisoners of war plan an elaborate escape from a German prison camp during World War II.',
                'release_year' => 1963,
                'genre' => 'War/Adventure',
                'external_link' => 'https://example.com/great-escape'
            ]);
    
            Movie::create([
                'title' => 'Night of the Living Dead',
                'description' => 'A group of people barricade themselves in an old farmhouse in rural Pennsylvania to survive the night.',
                'release_year' => 1968,
                'genre' => 'Horror',
                'external_link' => 'https://example.com/night-living-dead'
            ]);
        }
    }
}
