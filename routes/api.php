<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

// Temporary debug route to instantly return 201 to your NodeMCU
Route::post('/v1/smartguard/telemetry', function (Request $request) {
    Log::info('Incoming Data!', $request->all()); // Log it to storage/logs/laravel.log
    
    return response()->json([
        'status' => 'success',
        'message' => 'Telemetry received by HP Laptop!'
    ], 201); 
});