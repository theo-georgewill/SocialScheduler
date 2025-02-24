<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\PostController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Social Accounts
    Route::get('/social-accounts', [SocialAccountController::class, 'index']);
    Route::post('/social-accounts/connect', [SocialAccountController::class, 'connect']);
    Route::delete('/social-accounts/{id}', [SocialAccountController::class, 'disconnect']);

    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});

// File Upload Route
Route::post('/upload-files', [FileUploadController::class, 'upload']);
Route::get('/uploaded-files', [FileUploadController::class, 'index']); // Fetch stored files
