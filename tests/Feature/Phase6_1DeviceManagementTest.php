<?php

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('can list devices', function () {
    Device::create([
        'device_name' => 'Unit 1',
        'device_code' => 'SG-001',
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/devices');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.device_code', 'SG-001');
});

test('can create a device', function () {
    $payload = [
        'device_name' => 'New Unit',
        'device_code' => 'SG-NEW',
        'status' => 'active',
        'location' => 'Living Room',
    ];

    $response = $this->postJson('/api/v1/devices', $payload);

    $response->assertStatus(201);
    $this->assertDatabaseHas('devices', ['device_code' => 'SG-NEW']);
});

test('cannot create device with duplicate code', function () {
    Device::create([
        'device_name' => 'Unit 1',
        'device_code' => 'SG-001',
        'status' => 'active',
    ]);

    $payload = [
        'device_name' => 'Duplicate Unit',
        'device_code' => 'SG-001',
        'status' => 'active',
    ];

    $response = $this->postJson('/api/v1/devices', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['device_code']);
});

test('can update a device', function () {
    $device = Device::create([
        'device_name' => 'Unit 1',
        'device_code' => 'SG-001',
        'status' => 'active',
    ]);

    $payload = [
        'device_name' => 'Updated Unit',
        'status' => 'inactive',
    ];

    $response = $this->putJson("/api/v1/devices/{$device->id}", $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('devices', [
        'id' => $device->id,
        'device_name' => 'Updated Unit',
        'status' => 'inactive',
    ]);
});

test('can delete a device', function () {
    $device = Device::create([
        'device_name' => 'Unit 1',
        'device_code' => 'SG-001',
        'status' => 'active',
    ]);

    $response = $this->deleteJson("/api/v1/devices/{$device->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('devices', ['id' => $device->id]);
});

test('guest cannot access device management', function () {
    Auth::logout();

    $response = $this->getJson('/api/v1/devices');
    $response->assertStatus(401);
});

test('can get device latest reading', function () {
    $device = Device::create([
        'device_name' => 'Unit 1',
        'device_code' => 'SG-001',
        'status' => 'active',
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

    $response = $this->getJson("/api/v1/devices/{$device->id}/latest-reading");

    $response->assertStatus(200);
    $this->assertEquals(230, $response->json('voltage'));
});
