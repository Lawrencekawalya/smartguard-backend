<?php

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.smartguard.api_token', 'test-token-123');
    Config::set('services.smartguard.offline_after_seconds', 10);
    $this->headers = ['X-SmartGuard-Token' => 'test-token-123'];
});

test('unauthorized dashboard access fails', function () {
    $response = $this->getJson('/api/v1/smartguard/dashboard/status');
    $response->assertStatus(401);
});

test('status endpoint returns correct data', function () {
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
        'relay_status' => 1,
        'fault_status' => 'RUN',
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/status?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'device_code' => 'SmartGuard-MTR-001',
                'status' => 'RUN',
                'is_online' => true,
                'relay_status' => true,
            ],
        ]);
});

test('status endpoint marks stale devices offline', function () {
    $device = Device::create([
        'device_code' => 'SmartGuard-MTR-001',
        'device_name' => 'Unit 1',
        'last_seen_at' => now()->subSeconds(11),
    ]);

    $device->readings()->create([
        'voltage' => 230,
        'current' => 1,
        'real_power' => 230,
        'apparent_power' => 230,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
        'fault_status' => 'RUN',
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/status?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'device_code' => 'SmartGuard-MTR-001',
                'status' => 'OFFLINE',
                'is_online' => false,
                'relay_status' => false,
                'fault_status' => 'RUN',
            ],
        ]);
});

test('latest reading endpoint returns correct data', function () {
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
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/latest-reading?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonPath('data.voltage', 230.5);
});

test('fault history endpoint returns newest first', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->faults()->create(['fault_type' => 'OLD', 'occurred_at' => now()->subDay()]);
    $device->faults()->create(['fault_type' => 'NEW', 'occurred_at' => now()]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/fault-history?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.fault_type', 'NEW');
});

test('relay history endpoint returns newest first', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->relayLogs()->create(['action' => 'OLD', 'triggered_by' => 'USER', 'created_at' => now()->subDay()]);
    $device->relayLogs()->create(['action' => 'NEW', 'triggered_by' => 'USER', 'created_at' => now()]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/relay-history?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.action', 'NEW');
});

test('daily usage endpoint returns correct format', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->energySummaries()->create([
        'summary_date' => '2026-06-10',
        'daily_kwh' => 15.4,
        'monthly_kwh' => 400,
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/daily-usage?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonFragment(['date' => '2026-06-10', 'daily_kwh' => 15.4]);
});

test('monthly usage endpoint returns correct format', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->energySummaries()->create([
        'summary_date' => '2026-06-10',
        'daily_kwh' => 15.4,
        'monthly_kwh' => 430.2,
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/monthly-usage?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonFragment(['month' => '2026-06', 'monthly_kwh' => 430.2]);
});

test('voltage trend endpoint returns correct format', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->readings()->create([
        'voltage' => 235.5,
        'current' => 1,
        'real_power' => 200,
        'apparent_power' => 200,
        'power_factor' => 1,
        'energy_kwh' => 1,
        'relay_status' => true,
        'fault_status' => 'RUN',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/voltage-trend?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.value', 235.5);
});

test('current trend endpoint returns correct format', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->readings()->create([
        'voltage' => 230,
        'current' => 5.432,
        'real_power' => 200,
        'apparent_power' => 200,
        'power_factor' => 1,
        'energy_kwh' => 1,
        'relay_status' => true,
        'fault_status' => 'RUN',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/current-trend?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.value', 5.432);
});

test('power trend endpoint returns correct format', function () {
    $device = Device::create(['device_code' => 'SmartGuard-MTR-001', 'device_name' => 'Unit 1']);
    $device->readings()->create([
        'voltage' => 230,
        'current' => 1,
        'real_power' => 1250.5,
        'apparent_power' => 1300,
        'power_factor' => 0.96,
        'energy_kwh' => 1,
        'relay_status' => true,
        'fault_status' => 'RUN',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/smartguard/dashboard/power-trend?device_code=SmartGuard-MTR-001', $this->headers);

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.value', 1250.5);
});
