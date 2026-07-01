<?php

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.smartguard.api_token', 'test-token-123');
    Config::set('services.smartguard.offline_after_seconds', 10);
});

test('mobile alarm state is normal when device is healthy', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'last_seen_at' => now(),
    ]);

    $device->readings()->create([
        'voltage' => 230,
        'current' => 1,
        'real_power' => 230,
        'apparent_power' => 230,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => true,
        'fault_status' => 'RUN',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/mobile/devices/SmartGuard-MTR-001/alarm-state', [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('alarm_state', 'NORMAL')
        ->assertJsonPath('device_online', true);
});

test('mobile alarm state is active until acknowledged', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'last_seen_at' => now(),
    ]);
    $fault = $device->faults()->create([
        'fault_type' => 'OVERCURRENT DETECTED',
        'occurred_at' => now(),
    ]);

    $device->readings()->create([
        'voltage' => 230,
        'current' => 6,
        'real_power' => 1300,
        'apparent_power' => 1380,
        'power_factor' => 0.94,
        'energy_kwh' => 10,
        'relay_status' => false,
        'fault_status' => 'TRIP',
        'created_at' => now(),
    ]);

    $this->getJson('/api/v1/mobile/devices/SmartGuard-MTR-001/alarm-state', [
        'X-SmartGuard-Token' => 'test-token-123',
    ])->assertOk()
        ->assertJsonPath('alarm_state', 'FAULT_ACTIVE_UNACKED');

    $this->postJson("/api/v1/mobile/faults/{$fault->id}/acknowledge", [], [
        'X-SmartGuard-Token' => 'test-token-123',
    ])->assertOk();

    $this->getJson('/api/v1/mobile/devices/SmartGuard-MTR-001/alarm-state', [
        'X-SmartGuard-Token' => 'test-token-123',
    ])->assertOk()
        ->assertJsonPath('alarm_state', 'FAULT_ACTIVE_ACKED');
});

test('mobile alarm state reports offline device', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'last_seen_at' => now()->subSeconds(20),
    ]);

    $device->readings()->create([
        'voltage' => 230,
        'current' => 1,
        'real_power' => 230,
        'apparent_power' => 230,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => true,
        'fault_status' => 'RUN',
        'created_at' => now()->subSeconds(20),
    ]);

    $response = $this->getJson('/api/v1/mobile/devices/SmartGuard-MTR-001/alarm-state', [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('alarm_state', 'OFFLINE')
        ->assertJsonPath('device_online', false);
});

test('mobile alarm state prioritizes latest fault reading over stale telemetry', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'last_seen_at' => now()->subSeconds(20),
    ]);

    $device->readings()->create([
        'voltage' => 260,
        'current' => 0,
        'real_power' => 0,
        'apparent_power' => 0,
        'power_factor' => 0,
        'energy_kwh' => 10,
        'relay_status' => false,
        'device_status' => 'TRIP',
        'fault_reason' => 'OVERVOLTAGE',
        'fault_status' => 'TRIP',
        'created_at' => now()->subSeconds(20),
    ]);

    $response = $this->getJson('/api/v1/mobile/devices/SmartGuard-MTR-001/alarm-state', [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('alarm_state', 'FAULT_ACTIVE_UNACKED')
        ->assertJsonPath('device_online', false);
});

test('mobile alarm state includes six recent faults', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'last_seen_at' => now(),
    ]);

    foreach (range(1, 7) as $index) {
        $device->faults()->create([
            'fault_type' => "FAULT {$index}",
            'occurred_at' => now()->subMinutes($index),
            'resolved_at' => now()->subMinutes($index - 1),
        ]);
    }

    $response = $this->getJson('/api/v1/mobile/devices/SmartGuard-MTR-001/alarm-state', [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk()
        ->assertJsonCount(6, 'recent_faults')
        ->assertJsonPath('recent_faults.0.fault_type', 'FAULT 7')
        ->assertJsonPath('recent_faults.5.fault_type', 'FAULT 2');
});
