<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;


Route::middleware([
    EnsureFrontendRequestsAreStateful::class,  // Initializes session and CSRF protection for SPA requests
    StartSession::class, // explicitly start the session
    'api',
    SubstituteBindings::class,
    //'auth:sanctum'
])->group(function () {
    
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    

    Route::get('/auth/facebook/redirect', [SocialAuthController::class, 'redirectToFacebook']);
    Route::get('/auth/reddit/redirect', [SocialAuthController::class, 'redirectToReddit']);
    Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback']);
    Route::get('/auth/reddit/callback', [SocialAuthController::class, 'handleRedditCallback']);
    // Social Media Authentication Routes
    #Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirectToProvider']);
    #Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);

    // Social Accounts
    Route::get('/social-accounts', [SocialAccountController::class, 'index']);
    Route::post('/social-accounts/connect', [SocialAccountController::class, 'connect']);
    Route::delete('/social-accounts/{id}', [SocialAccountController::class, 'disconnect']);

    
    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // Routes that do not require session (if any)
    Route::post('/upload-files', [FileUploadController::class, 'upload']);
    Route::get('/uploaded-files', [FileUploadController::class, 'index']); // Fetch stored files
});
