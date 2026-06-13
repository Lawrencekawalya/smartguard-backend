<?php

use App\Models\Device;
use App\Models\EnergySummary;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.smartguard.api_token', 'test-token-123');
    $this->headers = ['X-SmartGuard-Token' => 'test-token-123'];
});

afterEach(function () {
    Carbon::setTestNow();
});

function telemetryPayload(float $energyKwh): array
{
    return [
        'device_code' => 'SmartGuard-MTR-001',
        'status' => 'RUN',
        'fault_reason' => 'NONE',
        'voltage' => 230,
        'current' => 5,
        'real_power' => 1150,
        'apparent_power' => 1200,
        'power_factor' => 0.96,
        'energy_kwh' => $energyKwh,
        'relay_status' => 1,
    ];
}

test('telemetry continuously accumulates daily and monthly energy summaries', function () {
    Carbon::setTestNow('2026-06-13 08:00:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(1500),
        $this->headers,
    )->assertCreated();

    Carbon::setTestNow('2026-06-13 08:05:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(1500.375),
        $this->headers,
    )->assertCreated();

    Carbon::setTestNow('2026-06-13 08:10:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(1500.625),
        $this->headers,
    )->assertCreated();

    $this->assertDatabaseCount('energy_summaries', 1);
    $this->assertDatabaseHas('energy_summaries', [
        'summary_date' => '2026-06-13',
        'daily_kwh' => 0.625,
        'monthly_kwh' => 0.625,
    ]);
});

test('telemetry starts a new daily summary after midnight', function () {
    Carbon::setTestNow('2026-06-13 23:55:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(100),
        $this->headers,
    );

    Carbon::setTestNow('2026-06-13 23:59:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(100.4),
        $this->headers,
    );

    Carbon::setTestNow('2026-06-14 00:05:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(100.7),
        $this->headers,
    );

    $this->assertDatabaseHas('energy_summaries', [
        'summary_date' => '2026-06-13',
        'daily_kwh' => 0.4,
        'monthly_kwh' => 0.4,
    ]);
    $this->assertDatabaseHas('energy_summaries', [
        'summary_date' => '2026-06-14',
        'daily_kwh' => 0.3,
        'monthly_kwh' => 0.7,
    ]);
});

test('a cumulative meter reset does not create negative consumption', function () {
    Carbon::setTestNow('2026-06-13 08:00:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(500),
        $this->headers,
    );

    Carbon::setTestNow('2026-06-13 08:05:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(501),
        $this->headers,
    );

    Carbon::setTestNow('2026-06-13 08:10:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(0),
        $this->headers,
    );

    Carbon::setTestNow('2026-06-13 08:15:00');
    $this->postJson(
        '/api/v1/smartguard/telemetry',
        telemetryPayload(0.2),
        $this->headers,
    );

    $this->assertDatabaseHas('energy_summaries', [
        'summary_date' => '2026-06-13',
        'daily_kwh' => 1.2,
        'monthly_kwh' => 1.2,
    ]);
});

test('energy summaries can be rebuilt from historical device readings', function () {
    $device = Device::create([
        'device_name' => 'Test Unit',
        'device_code' => 'SmartGuard-MTR-001',
    ]);

    foreach ([
        ['2026-06-13 08:00:00', 100],
        ['2026-06-13 12:00:00', 102.5],
        ['2026-06-14 08:00:00', 104],
    ] as [$timestamp, $energy]) {
        $device->readings()->create([
            'voltage' => 230,
            'current' => 5,
            'real_power' => 1150,
            'apparent_power' => 1200,
            'power_factor' => 0.96,
            'energy_kwh' => $energy,
            'relay_status' => true,
            'fault_status' => 'RUN',
            'created_at' => $timestamp,
        ]);
    }

    EnergySummary::create([
        'device_id' => $device->id,
        'summary_date' => '2026-06-13',
        'daily_kwh' => 999,
        'monthly_kwh' => 999,
    ]);

    $this->artisan('energy:rebuild-summaries', [
        '--device' => $device->device_code,
    ])->assertSuccessful();

    $this->assertDatabaseHas('energy_summaries', [
        'summary_date' => '2026-06-13',
        'daily_kwh' => 2.5,
        'monthly_kwh' => 2.5,
    ]);
    $this->assertDatabaseHas('energy_summaries', [
        'summary_date' => '2026-06-14',
        'daily_kwh' => 1.5,
        'monthly_kwh' => 4,
    ]);
});
