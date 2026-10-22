<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\InsuranceController;



// Services API Routes
Route::get('/services', [ServiceController::class, 'getActiveServices']);


Route::get('/countries', [LocationController::class, 'countries']);
Route::get('/provinces/{country}', [LocationController::class, 'provinces']);
Route::get('/districts/{province}', [LocationController::class, 'districts']);
Route::get('/sectors/{district}', [LocationController::class, 'sectors']);


///INSURANCE FETCHING ROUTE///

Route::prefix('insurance')->group(function () {
    Route::get('/providers', [InsuranceController::class, 'providers']);
    Route::get('/categories/{providerId}', [InsuranceController::class, 'categories']);
    Route::get('/packages/{categoryId}', [InsuranceController::class, 'packages']);
    Route::get('/package/{packageId}', [InsuranceController::class, 'package']);
});
