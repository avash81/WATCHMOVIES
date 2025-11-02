<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'release_year',
        'genre',
        'poster_url',
        'trailer_url',
        'external_link',
        'cast',
        'budget',
        'box_office',
        'download_480p',
        'download_720p',
        'download_1080p',
        'category',
    ];
}
