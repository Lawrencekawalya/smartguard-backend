<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TelemetryController;
use App\Http\Controllers\Api\DashboardController;

use App\Http\Middleware\CheckSmartGuardToken;

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\FaultSettingController;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::apiResource('devices', DeviceController::class)->names('api.devices');
    Route::get('devices/{device}/latest-reading', [DeviceController::class, 'latestReading'])->name('api.devices.latest-reading');
    Route::get('devices/{device}/faults', [DeviceController::class, 'faults'])->name('api.devices.faults');
    Route::get('devices/{device}/relay-logs', [DeviceController::class, 'relayLogs'])->name('api.devices.relay-logs');

    Route::apiResource('fault-settings', FaultSettingController::class)->only(['index', 'show', 'update'])->names('api.fault-settings');
});

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
        Route::get('/voltage-trend', [DashboardController::class, 'voltageTrend']);
        Route::get('/current-trend', [DashboardController::class, 'currentTrend']);
        Route::get('/power-trend', [DashboardController::class, 'powerTrend']);
    });
});
