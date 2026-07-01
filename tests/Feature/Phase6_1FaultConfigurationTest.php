<?php

use App\Models\Device;
use App\Models\FaultSetting;
use App\Models\User;
use Database\Seeders\FaultSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->seed(FaultSettingSeeder::class);
    Config::set('services.smartguard.api_token', 'test-token-123');
});

test('seeder creates default settings', function () {
    $this->assertDatabaseHas('fault_settings', ['fault_code' => 'OVERVOLTAGE']);
    $this->assertDatabaseHas('fault_settings', ['fault_code' => 'OVERCURRENT']);
});

test('can retrieve fault settings', function () {
    $response = $this->getJson('/api/v1/fault-settings');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

test('can update fault setting', function () {
    $setting = FaultSetting::where('fault_code', 'OVERVOLTAGE')->first();

    $payload = [
        'max_value' => 255,
        'enabled' => true,
    ];

    $response = $this->putJson("/api/v1/fault-settings/{$setting->id}", $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('fault_settings', [
        'id' => $setting->id,
        'max_value' => 255,
    ]);
});

test('can update current threshold up to twenty amps', function () {
    $setting = FaultSetting::where('fault_code', 'OVERCURRENT')->first();

    $response = $this->putJson("/api/v1/fault-settings/{$setting->id}", [
        'max_value' => 20,
        'enabled' => true,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('fault_settings', [
        'id' => $setting->id,
        'max_value' => 20,
    ]);
});

test('unsafe hardware threshold update is rejected', function () {
    $setting = FaultSetting::where('fault_code', 'OVERVOLTAGE')->first();

    $response = $this->putJson("/api/v1/fault-settings/{$setting->id}", [
        'max_value' => 260,
    ]);

    $response->assertStatus(422);
});

test('telemetry detects overvoltage based on database setting', function () {
    $setting = FaultSetting::where('fault_code', 'OVERVOLTAGE')->first();
    $setting->update(['max_value' => 250]);

    $payload = [
        'device_code' => 'SG-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 255, // Above threshold
        'current' => 1,
        'real_power' => 255,
        'apparent_power' => 255,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
    ];

    $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $this->assertDatabaseHas('faults', [
        'fault_type' => 'OVERVOLTAGE SURGE',
        'resolved_at' => null,
    ]);
});

test('telemetry detects undervoltage based on database setting', function () {
    $setting = FaultSetting::where('fault_code', 'OVERVOLTAGE')->first();
    $setting->update(['min_value' => 190]);

    $payload = [
        'device_code' => 'SG-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 180, // Below threshold
        'current' => 1,
        'real_power' => 180,
        'apparent_power' => 180,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
    ];

    $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $this->assertDatabaseHas('faults', [
        'fault_type' => 'UNDERVOLTAGE BROWNOUT',
        'resolved_at' => null,
    ]);
});

test('telemetry detects overcurrent based on database setting', function () {
    $setting = FaultSetting::where('fault_code', 'OVERCURRENT')->first();
    $setting->update(['max_value' => 10]);

    $payload = [
        'device_code' => 'SG-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 230,
        'current' => 12, // Above threshold
        'real_power' => 2760,
        'apparent_power' => 2760,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
    ];

    $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $this->assertDatabaseHas('faults', [
        'fault_type' => 'OVERCURRENT DETECTED',
        'resolved_at' => null,
    ]);
});

test('telemetry resolves faults when values return to normal', function () {
    $device = Device::create(['device_code' => 'SG-001', 'device_name' => 'Unit 1']);
    $device->faults()->create([
        'fault_type' => 'OVERVOLTAGE SURGE',
        'occurred_at' => now()->subMinute(),
    ]);

    $payload = [
        'device_code' => 'SG-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 230, // Normal
        'current' => 1,
        'real_power' => 230,
        'apparent_power' => 230,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
    ];

    $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $this->assertDatabaseMissing('faults', [
        'fault_type' => 'OVERVOLTAGE SURGE',
        'resolved_at' => null,
    ]);
});

test('reset success packet resolves faults without creating a zero voltage fault', function () {
    $device = Device::create(['device_code' => 'SG-001', 'device_name' => 'Unit 1']);
    $device->faults()->create([
        'fault_type' => 'OVERVOLTAGE SURGE',
        'occurred_at' => now()->subMinute(),
    ]);

    $payload = [
        'device_code' => 'SG-001',
        'status' => 'RUN',
        'fault_reason' => 'RESET_SUCCESS',
        'voltage' => 0,
        'current' => 0,
        'real_power' => 0,
        'apparent_power' => 0,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
    ];

    $response = $this->postJson('/api/v1/smartguard/telemetry', $payload, [
        'X-SmartGuard-Token' => 'test-token-123',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseMissing('faults', [
        'fault_type' => 'OVERVOLTAGE SURGE',
        'resolved_at' => null,
    ]);
    $this->assertDatabaseMissing('faults', [
        'fault_type' => 'UNDERVOLTAGE BROWNOUT',
        'resolved_at' => null,
    ]);
});
