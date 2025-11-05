<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        // TMDB fields
        'tmdb_id',
        'title',
        'overview',
        'poster_path',
        'backdrop_path',
        'release_date',
        'vote_average',
        'vote_count',
        'genre_ids',
        'popularity',
        'original_language',
        'original_title',
        'video',
        'adult',
        'details',
        
        // Original fields
        'description',
        'release_year',
        'genre',
        'external_link',
        'poster_url',
        'trailer_url',
        'cast',
        'budget',
        'box_office',
        'download_480p',
        'download_720p',
        'download_1080p',
        'category'
    ];

    protected $casts = [
        // TMDB casts
        'genre_ids' => 'array',
        'vote_average' => 'float',
        'popularity' => 'float',
        'video' => 'boolean',
        'adult' => 'boolean',
        'details' => 'array',
        'release_date' => 'date',
        
        // Original casts
        'budget' => 'float',
        'box_office' => 'float',
    ];
}