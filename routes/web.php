<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This is an API-only application. All web routes return 404.
| Please use the API routes in routes/api.php instead.
|
*/

Route::fallback(fn () => response()->json([
    'message' => 'This is an API-only application. Please use the API endpoints.',
    'documentation' => '/api/documentation', // Update this with your actual API documentation URL
], 404));
