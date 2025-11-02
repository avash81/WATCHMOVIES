<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['movie_id', 'user_id', 'parent_id', 'body'];

    public function movie() { return $this->belongsTo(Movie::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function parent() { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function replies() { return $this->hasMany(Comment::class, 'parent_id'); }

    public function scopeForMovie($query, $movieId) {
        return $query->where('movie_id', $movieId)->whereNull('parent_id')->with(['replies.user', 'user']);
    }
}