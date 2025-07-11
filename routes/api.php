<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Location routes
use App\Http\Controllers\LocationController;
Route::get('/locations', [LocationController::class, 'index']);
Route::post('/locations', [LocationController::class, 'store']);

// Sensor routes
use App\Http\Controllers\SensorController;
Route::get('/sensors', [SensorController::class, 'index']);
Route::post('/sensors', [SensorController::class, 'store']);

// Visitor routes
use App\Http\Controllers\VisitorController;
Route::get('/visitors', [VisitorController::class, 'index']);
Route::post('/visitors', [VisitorController::class, 'store']);

// Summary routes
use App\Http\Controllers\SummaryController;
Route::get('/summary', [SummaryController::class, 'index']);
