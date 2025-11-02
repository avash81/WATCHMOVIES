<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index($movieId)
    {
        $comments = Comment::where('movie_id', $movieId)
            ->whereNull('parent_id')
            ->with(['replies.user', 'user'])
            ->get();
        return response()->json($comments);
    }

    public function store(Request $request, $movieId)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        $comment = Comment::create([
            'movie_id' => $movieId,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'body' => $request->body,
        ]);

        return response()->json($comment->load('user'));
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $comment->delete();
        return response()->json(['message' => 'Deleted']);
    }
}