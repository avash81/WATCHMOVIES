<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\CommentController; // ← Add
use App\Http\Controllers\AdminController;   // ← Add

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


    Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function () {
        return auth()->user();
    });

    Route::get('/movies/{id}/comments', [CommentController::class, 'index']);
    Route::post('/movies/{id}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    Route::prefix('admin')->group(function () {
        Route::get('/movies', [AdminController::class, 'index']);
        Route::post('/movies', [AdminController::class, 'store']);
    });
});