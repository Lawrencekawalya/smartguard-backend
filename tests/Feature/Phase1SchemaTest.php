<?php

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('device model can be created', function () {
    $device = Device::create([
        'device_name' => 'SmartGuard Unit 1',
        'device_code' => 'SmartGuard-MTR-001',
        'location' => 'Main Office',
        'status' => 'active',
        'firmware_version' => '1.0.0',
        'ip_address' => '192.168.1.100',
    ]);

    expect($device->exists)->toBeTrue();
    expect($device->device_code)->toBe('SmartGuard-MTR-001');
});

test('device reading can be created and relates to device', function () {
    $device = Device::create([
        'device_name' => 'SmartGuard Unit 1',
        'device_code' => 'SmartGuard-MTR-001',
    ]);

    $reading = $device->readings()->create([
        'voltage' => 230.5,
        'current' => 5.2,
        'real_power' => 1200.0,
        'apparent_power' => 1250.0,
        'power_factor' => 0.96,
        'energy_kwh' => 150.123456,
        'relay_status' => true,
        'fault_status' => 'NONE',
        'created_at' => now(),
    ]);

    expect($reading->exists)->toBeTrue();
    expect($reading->device_id)->toBe($device->id);
    expect($device->readings)->toHaveCount(1);
});

test('fault can be created and relates to device', function () {
    $device = Device::create([
        'device_name' => 'SmartGuard Unit 1',
        'device_code' => 'SmartGuard-MTR-001',
    ]);

    $fault = $device->faults()->create([
        'fault_type' => 'OVERVOLTAGE SURGE',
        'description' => 'Voltage exceeded 250V',
        'occurred_at' => now(),
    ]);

    expect($fault->exists)->toBeTrue();
    expect($fault->device_id)->toBe($device->id);
    expect($device->faults)->toHaveCount(1);
});

test('relay log can be created and relates to device', function () {
    $device = Device::create([
        'device_name' => 'SmartGuard Unit 1',
        'device_code' => 'SmartGuard-MTR-001',
    ]);

    $log = $device->relayLogs()->create([
        'action' => 'AUTO_TRIP',
        'triggered_by' => 'HARDWARE_ENGINE',
        'created_at' => now(),
    ]);

    expect($log->exists)->toBeTrue();
    expect($log->device_id)->toBe($device->id);
    expect($device->relayLogs)->toHaveCount(1);
});

test('energy summary can be created and relates to device', function () {
    $device = Device::create([
        'device_name' => 'SmartGuard Unit 1',
        'device_code' => 'SmartGuard-MTR-001',
    ]);

    $summary = $device->energySummaries()->create([
        'summary_date' => now()->format('Y-m-d'),
        'daily_kwh' => 10.5,
        'monthly_kwh' => 300.2,
    ]);

    expect($summary->exists)->toBeTrue();
    expect($summary->device_id)->toBe($device->id);
    expect($device->energySummaries)->toHaveCount(1);
});
