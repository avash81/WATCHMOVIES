<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController; // <-- Ensure this line is present and correct

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// movie-api/routes/api.php
// temporary 111
Route::get('/test', function () {
    return response()->json(['status' => 'success', 'message' => 'API is working!']);
});
// ... (your other Movie routes)
// temporary 111
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
