<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class AdminController extends Controller
{
    public function index()
    {
        return response()->json(Movie::latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'release_year' => 'required|integer',
            'genre' => 'required',
            'category' => 'required',
        ]);

        $movie = Movie::create($request->all());
        return response()->json($movie);
    }
}