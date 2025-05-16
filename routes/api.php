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
    //1. Authentication & User Management
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user/settings', [AuthController::class, 'resetPassword']);

    
    //2. Social Authentication & Account Integration
    Route::get('/auth/facebook/redirect', [SocialAuthController::class, 'redirectToFacebook']);
    Route::get('/auth/reddit/redirect', [SocialAuthController::class, 'redirectToReddit']);
    Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback']);
    Route::get('/auth/reddit/callback', [SocialAuthController::class, 'handleRedditCallback']);

    // Social Accounts
    Route::get('/social-accounts', [SocialAccountController::class, 'index']);
    Route::get('/{provider}/connect', [SocialAccountController::class, 'connect']);

    Route::get('/connect/facebook', [SocialAccountController::class, 'connectFacebookAccount']);
    Route::delete('/disconnect/facebook/{id}', [SocialAccountController::class, 'disconnectFacebookAccount']);
    Route::get('/connect/reddit', [SocialAccountController::class, 'connectRedditAccount']);
    Route::delete('/disconnect/{provider}/{id}', [SocialAccountController::class, 'disconnect']);
    
    // 3. Post Creation & Scheduling
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/post/reddit/{id}', [PostController::class, 'publishToReddit']);
    Route::post('/publish/reddit/{post}', [PostController::class, 'publishToReddit']);
    Route::post('/publish/facebook/{post}', [PostController::class, 'publishToFacebook']);


    // Routes that do not require session (if any)
    Route::post('/upload-files', [FileUploadController::class, 'upload']);
    Route::get('/uploaded-files', [FileUploadController::class, 'index']); // Fetch stored files
});
