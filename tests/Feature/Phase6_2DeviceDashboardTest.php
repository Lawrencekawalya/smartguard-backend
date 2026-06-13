<?php

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('devices index page loads', function () {
    $response = $this->actingAs($this->user)->get('/devices');
    $response->assertStatus(200);
});

test('device create page loads', function () {
    $response = $this->actingAs($this->user)->get('/devices/create');
    $response->assertStatus(200);
});

test('device edit page loads', function () {
    $device = Device::create([
        'device_name' => 'Unit 1',
        'device_code' => 'SG-001',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->user)->get("/devices/{$device->id}/edit");
    $response->assertStatus(200);
});

test('device show page loads', function () {
    $device = Device::create([
        'device_name' => 'Unit 1',
        'device_code' => 'SG-001',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->user)->get("/devices/{$device->id}");
    $response->assertStatus(200);
});

test('can update a device through web route', function () {
    $device = Device::create([
        'device_name' => 'Original Name',
        'device_code' => 'SG-ORIG',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->user)
        ->put("/devices/{$device->id}", [
            'device_name' => 'Updated Name',
            'device_code' => 'SG-ORIG',
            'status' => 'inactive',
        ]);

    $response->assertRedirect('/devices');
    $this->assertDatabaseHas('devices', [
        'id' => $device->id,
        'device_name' => 'Updated Name',
        'status' => 'inactive',
    ]);
});
