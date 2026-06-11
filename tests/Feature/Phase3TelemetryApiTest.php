<?php

use App\Models\Device;
use App\Models\DeviceReading;
use App\Models\Fault;
use App\Models\RelayLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.smartguard.api_token', 'test-token-123');
});

test('unauthorized request fails', function () {
    $response = $this->postJson('/api/v1/smartguard/telemetry', []);
    $response->assertStatus(401);
});

test('validation failure returns 422', function () {
    $response = $this->postJson('/api/v1/smartguard/telemetry', [], [
        'X-SmartGuard-Token' => 'test-token-123'
    ]);
    $response->assertStatus(422);
});

test('device auto creation on first telemetry', function () {
    $payload = [
        'device_code' => 'SmartGuard-MTR-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 238.6,
        'current' => 5.231,
        'real_power' => 1180,
        'apparent_power' => 1235,
        'power_factor' => 0.95,
        'energy_kwh' => 1542.25,
        'relay_status' => 1,
    ];

    $response = $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123'
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('devices', ['device_code' => 'SmartGuard-MTR-001']);
});

test('telemetry storage', function () {
    $payload = [
        'device_code' => 'SmartGuard-MTR-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 238.6,
        'current' => 5.231,
        'real_power' => 1180,
        'apparent_power' => 1235,
        'power_factor' => 0.95,
        'energy_kwh' => 1542.25,
        'relay_status' => 1,
    ];

    $response = $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123'
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('device_readings', [
        'voltage' => 238.6,
        'current' => 5.231,
    ]);
});

test('relay transition logging', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    
    // First reading (Relay ON)
    $this->postJson('/api/v1/smartguard/telemetry', [
        'device_code' => 'SmartGuard-MTR-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 230,
        'current' => 1,
        'real_power' => 230,
        'apparent_power' => 230,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
    ], ['X-SmartGuard-Token' => 'test-token-123']);

    // Second reading (Relay OFF via TRIP)
    $this->postJson('/api/v1/smartguard/telemetry', [
        'device_code' => 'SmartGuard-MTR-001',
        'status' => 'TRIP',
        'fault_reason' => 'OVERVOLTAGE',
        'voltage' => 260,
        'current' => 0,
        'real_power' => 0,
        'apparent_power' => 0,
        'power_factor' => 0,
        'energy_kwh' => 10,
        'relay_status' => 0,
    ], ['X-SmartGuard-Token' => 'test-token-123']);

    $this->assertDatabaseHas('relay_logs', [
        'device_id' => $device->id,
        'action' => 'AUTO_TRIP',
        'triggered_by' => 'HARDWARE_ENGINE'
    ]);
});

test('fault creation on TRIP', function () {
    $payload = [
        'device_code' => 'SmartGuard-MTR-001',
        'status' => 'TRIP',
        'fault_reason' => 'OVERVOLTAGE',
        'voltage' => 260,
        'current' => 0,
        'real_power' => 0,
        'apparent_power' => 0,
        'power_factor' => 0,
        'energy_kwh' => 10,
        'relay_status' => 0,
    ];

    $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123'
    ]);

    $this->assertDatabaseHas('faults', [
        'fault_type' => 'OVERVOLTAGE',
        'resolved_at' => null
    ]);
});

test('fault resolution on RUN', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $fault = $device->faults()->create([
        'fault_type' => 'OVERVOLTAGE',
        'occurred_at' => now()->subMinute(),
    ]);

    $payload = [
        'device_code' => 'SmartGuard-MTR-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 230,
        'current' => 1,
        'real_power' => 230,
        'apparent_power' => 230,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
    ];

    $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123'
    ]);

    $this->assertDatabaseMissing('faults', [
        'id' => $fault->id,
        'resolved_at' => null
    ]);
});

test('latest telemetry endpoint returns correct data', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->readings()->create([
        'voltage' => 230.5,
        'current' => 5.2,
        'real_power' => 1200,
        'apparent_power' => 1250,
        'power_factor' => 0.96,
        'energy_kwh' => 150,
        'relay_status' => true,
        'fault_status' => 'RUN',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/smartguard/telemetry/latest?device_code=SmartGuard-MTR-001', [
        'X-SmartGuard-Token' => 'test-token-123'
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('data.voltage', 230.5);
});
