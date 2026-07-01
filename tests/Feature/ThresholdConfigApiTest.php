<?php

use App\Models\Device;
use App\Models\FaultSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.smartguard.api_token', 'test-token-123');

    FaultSetting::create([
        'parameter' => 'voltage',
        'fault_code' => 'VOLTAGE',
        'min_value' => 185,
        'max_value' => 258,
        'unit' => 'V',
        'enabled' => true,
    ]);

    FaultSetting::create([
        'parameter' => 'current',
        'fault_code' => 'CURRENT',
        'min_value' => 0,
        'max_value' => 5,
        'unit' => 'A',
        'enabled' => true,
    ]);

    FaultSetting::create([
        'parameter' => 'power_factor',
        'fault_code' => 'LOW_POWER_FACTOR',
        'min_value' => 0.2,
        'max_value' => 1,
        'unit' => 'PF',
        'enabled' => true,
    ]);

    FaultSetting::create([
        'parameter' => 'real_power',
        'fault_code' => 'OVERLOAD',
        'min_value' => 0,
        'max_value' => 1200,
        'unit' => 'W',
        'enabled' => true,
    ]);

    FaultSetting::create([
        'parameter' => 'apparent_power',
        'fault_code' => 'OVERAPPARENT',
        'min_value' => 0,
        'max_value' => 1500,
        'unit' => 'VA',
        'enabled' => true,
    ]);
});

test('device can fetch threshold config frame', function () {
    $response = $this->get('/api/v1/smartguard/config?device_code=SmartGuard-MTR-001', [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk();
    expect($response->getContent())->toMatch('/^CFG,\d+,5\.000,185\.0,258\.0,0\.20,1200\.0,1500\.0$/');

    $this->assertDatabaseHas('devices', [
        'device_code' => 'SmartGuard-MTR-001',
        'threshold_config_status' => 'pending',
    ]);
});

test('device can acknowledge threshold config', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'threshold_config_version' => 123,
        'threshold_config_status' => 'pending',
    ]);

    $response = $this->postJson('/api/v1/smartguard/config/ack', [
        'device_code' => $device->device_code,
        'version' => 123,
        'status' => 'ACK',
    ], [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('threshold_config_ack_version', 123)
        ->assertJsonPath('threshold_config_status', 'synced');
});

test('device can report threshold config error', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'threshold_config_version' => 123,
        'threshold_config_status' => 'pending',
    ]);

    $response = $this->postJson('/api/v1/smartguard/config/ack', [
        'device_code' => $device->device_code,
        'version' => 123,
        'status' => 'ERR',
        'message' => 'UNSAFE_LIMITS',
    ], [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('threshold_config_status', 'failed')
        ->assertJsonPath('threshold_config_error', 'UNSAFE_LIMITS');
});

test('device can report active board threshold status without prior config push', function () {
    $response = $this->postJson('/api/v1/smartguard/config/status', [
        'device_code' => 'SmartGuard-MTR-001',
        'version' => 0,
        'max_current' => 5.0,
        'min_voltage' => 185.0,
        'max_voltage' => 258.0,
        'min_power_factor' => 0.0,
        'max_real_power' => 0.0,
        'max_apparent_power' => 0.0,
    ], [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('threshold_config_ack_version', 0)
        ->assertJsonPath('threshold_config_ack_payload.max_current', 5)
        ->assertJsonPath('threshold_config_status', 'board_reported');

    $this->assertDatabaseHas('devices', [
        'device_code' => 'SmartGuard-MTR-001',
        'threshold_config_ack_version' => 0,
        'threshold_config_status' => 'board_reported',
    ]);
});
