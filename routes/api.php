<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// File Upload Route
Route::post('/upload-files', [FileUploadController::class, 'upload']);
Route::get('/uploaded-files', [FileUploadController::class, 'index']); // Fetch stored files
