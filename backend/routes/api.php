<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OperationalController;
use App\Http\Controllers\ReadingUploadController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/demo', [AuthController::class, 'demo'])->middleware('throttle:10,1');
Route::get('/health/readiness', [OperationalController::class, 'readiness']);
Route::get('/samples/energy_readings.csv', [OperationalController::class, 'sample']);
Route::get('/docs', [OperationalController::class, 'docs']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/readings/upload', [ReadingUploadController::class, 'upload'])->middleware('demo.readonly');
    Route::get('/imports', [ImportController::class, 'index']);
    Route::get('/dashboard/kpis', [DashboardController::class, 'kpis']);
    Route::get('/dashboard/series', [DashboardController::class, 'series']);
    Route::get('/alerts', [AlertController::class, 'index']);
});
