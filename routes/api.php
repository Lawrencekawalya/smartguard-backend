<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TelemetryController;

use App\Http\Middleware\CheckSmartGuardToken;

Route::prefix('v1/smartguard')->middleware(CheckSmartGuardToken::class)->group(function () {
    Route::post('/telemetry', [TelemetryController::class, 'store']);
    Route::get('/telemetry/latest', [TelemetryController::class, 'latest']);
});
