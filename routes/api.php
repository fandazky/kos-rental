<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingOwnerController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profiles', [AuthController::class, 'userProfile']);    
});

Route::middleware(['api', 'auth:api'])->group(function() {
    Route::group(['prefix' => 'owner', 'middleware' => 'isOwner'], function() {
        Route::get('/listings', [ListingOwnerController::class, 'index']);
        Route::post('/listings', [ListingOwnerController::class, 'store']);
        Route::patch('/listings/{id}', [ListingOwnerController::class, 'update']);
        Route::delete('/listings/{id}', [ListingOwnerController::class, 'destroy']);
    });
});

Route::middleware(['api', 'auth:api'])->group(function() {
    Route::group(['prefix' => 'user', 'middleware' => 'isUser'], function() {
        Route::get('/listings', [ListingController::class, 'index']);
        Route::get('/listings/{id}', [ListingController::class, 'show']);
        Route::post('/listings/{id}/availabilities', [ListingController::class, 'availability']);
    });
});