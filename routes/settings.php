<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Models\Device;
use App\Models\FaultSetting;
use App\Services\EnergyAnalyticsService;
use App\Services\ThresholdConfigService;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    Route::get('settings/fault-thresholds', function (ThresholdConfigService $thresholdConfigService) {
        return Inertia\Inertia::render('settings/FaultThresholds', [
            'settings' => FaultSetting::all(),
            'pendingConfig' => $thresholdConfigService->getThresholds(),
            'devices' => Device::query()
                ->select([
                    'device_name',
                    'device_code',
                    'threshold_config_version',
                    'threshold_config_ack_version',
                    'threshold_config_ack_payload',
                    'threshold_config_status',
                    'threshold_config_error',
                    'threshold_config_synced_at',
                ])
                ->orderBy('device_name')
                ->get(),
        ]);
    })->name('fault-thresholds.edit');

    Route::get('settings/energy', function (EnergyAnalyticsService $energyService) {
        return Inertia\Inertia::render('settings/Energy', [
            'setting' => $energyService->getSetting(),
        ]);
    })->name('energy-settings.edit');
});
