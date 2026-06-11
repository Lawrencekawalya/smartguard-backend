<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TelemetryController;
use App\Http\Controllers\Api\DashboardController;

use App\Http\Middleware\CheckSmartGuardToken;

Route::prefix('v1/smartguard')->middleware(CheckSmartGuardToken::class)->group(function () {
    Route::post('/telemetry', [TelemetryController::class, 'store']);
    Route::get('/telemetry/latest', [TelemetryController::class, 'latest']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/status', [DashboardController::class, 'status']);
        Route::get('/latest-reading', [DashboardController::class, 'latestReading']);
        Route::get('/latest-fault', [DashboardController::class, 'latestFault']);
        Route::get('/fault-history', [DashboardController::class, 'faultHistory']);
        Route::get('/relay-history', [DashboardController::class, 'relayHistory']);
        Route::get('/daily-usage', [DashboardController::class, 'dailyUsage']);
        Route::get('/monthly-usage', [DashboardController::class, 'monthlyUsage']);
    });
});
