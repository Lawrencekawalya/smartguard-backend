<?php

use App\Models\Device;
use App\Models\DeviceReading;
use App\Models\EnergySetting;
use App\Models\EnergySummary;
use App\Models\Fault;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    EnergySetting::create([
        'tariff_rate' => 805,
        'currency' => 'UGX',
        'description' => 'Test Tariff',
    ]);

    $this->device = Device::create([
        'device_name' => 'Test Unit',
        'device_code' => 'SG-TEST',
        'status' => 'active',
    ]);
});

test('can retrieve energy summary', function () {
    EnergySummary::create([
        'device_id' => $this->device->id,
        'summary_date' => Carbon::today(),
        'daily_kwh' => 10.5,
        'monthly_kwh' => 10.5,
    ]);

    $response = $this->getJson('/api/v1/energy/summary');

    $response->assertStatus(200)
        ->assertJsonPath('today_kwh', 10.5)
        ->assertJsonPath('estimated_cost', 10.5 * 805)
        ->assertJsonPath('cost_analysis.0.cost', 10.5 * 805)
        ->assertJsonPath('currency', 'UGX');
});

test('can retrieve daily analytics', function () {
    EnergySummary::create([
        'device_id' => $this->device->id,
        'summary_date' => Carbon::today(),
        'daily_kwh' => 5,
        'monthly_kwh' => 5,
    ]);

    $response = $this->getJson('/api/v1/energy/daily');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can retrieve energy report with peak power and faults', function () {
    $date = Carbon::today()->toDateString();

    EnergySummary::create([
        'device_id' => $this->device->id,
        'summary_date' => $date,
        'daily_kwh' => 10,
        'monthly_kwh' => 10,
    ]);

    DeviceReading::create([
        'device_id' => $this->device->id,
        'voltage' => 230,
        'current' => 5,
        'real_power' => 1150, // Peak
        'apparent_power' => 1150,
        'power_factor' => 1,
        'energy_kwh' => 10,
        'relay_status' => 1,
        'fault_status' => 'RUN',
        'created_at' => Carbon::today()->setHour(12),
    ]);

    Fault::create([
        'device_id' => $this->device->id,
        'fault_type' => 'OVERVOLTAGE',
        'occurred_at' => Carbon::today()->setHour(10),
    ]);

    $response = $this->getJson('/api/v1/energy/report');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.date', $date)
        ->assertJsonPath('data.0.energy_used', 10)
        ->assertJsonPath('data.0.estimated_cost', 8050)
        ->assertJsonPath('data.0.peak_power', 1150)
        ->assertJsonPath('data.0.fault_count', 1);
});

test('can update energy settings', function () {
    $payload = [
        'tariff_rate' => 900,
        'currency' => 'USD',
        'description' => 'New Tariff',
    ];

    $response = $this->putJson('/api/v1/energy/settings', $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('energy_settings', ['tariff_rate' => 900]);
});

test('energy analytics date range must be valid', function () {
    $this->getJson('/api/v1/energy/summary?start_date=2026-06-12&end_date=2026-06-10')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end_date');
});

test('can export filtered energy report as csv and pdf', function () {
    EnergySummary::create([
        'device_id' => $this->device->id,
        'summary_date' => '2026-06-10',
        'daily_kwh' => 15.4,
        'monthly_kwh' => 15.4,
    ]);
    EnergySummary::create([
        'device_id' => $this->device->id,
        'summary_date' => '2026-06-11',
        'daily_kwh' => 18.2,
        'monthly_kwh' => 33.6,
    ]);

    $csv = $this->get('/api/v1/energy/export/csv?start_date=2026-06-10&end_date=2026-06-10');
    $csv->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($csv->getContent())
        ->toContain('2026-06-10')
        ->not->toContain('2026-06-11');

    $pdf = $this->get('/api/v1/energy/export/pdf?start_date=2026-06-10&end_date=2026-06-11');
    $pdf->assertOk()
        ->assertHeader('content-type', 'application/pdf');
    expect($pdf->getContent())->toStartWith('%PDF-1.4');
});

test('energy report paginates by 25 while exports include the full filtered report', function () {
    $startDate = Carbon::parse('2026-05-01');

    foreach (range(0, 29) as $day) {
        EnergySummary::create([
            'device_id' => $this->device->id,
            'summary_date' => $startDate->copy()->addDays($day)->toDateString(),
            'daily_kwh' => $day + 1,
            'monthly_kwh' => $day + 1,
        ]);
    }

    $firstPage = $this->getJson(
        '/api/v1/energy/report?start_date=2026-05-01&end_date=2026-05-30&page=1'
    );
    $firstPage->assertOk()
        ->assertJsonCount(25, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 25)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('meta.total', 30);

    $secondPage = $this->getJson(
        '/api/v1/energy/report?start_date=2026-05-01&end_date=2026-05-30&page=2'
    );
    $secondPage->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.from', 26)
        ->assertJsonPath('meta.to', 30);

    $csv = $this->get(
        '/api/v1/energy/export/csv?start_date=2026-05-01&end_date=2026-05-30'
    );
    expect($csv->getContent())
        ->toContain('2026-05-30')
        ->toContain('2026-05-01');

    $pdf = $this->get(
        '/api/v1/energy/export/pdf?start_date=2026-05-01&end_date=2026-05-30'
    );
    expect($pdf->getContent())
        ->toContain('2026-05-30')
        ->toContain('2026-05-01');
});

test('energy settings page is available to authenticated users', function () {
    $this->get('/settings/energy')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/Energy')
            ->where('setting.currency', 'UGX')
        );
});

test('guest cannot access energy analytics', function () {
    auth()->logout();

    $response = $this->getJson('/api/v1/energy/summary');
    $response->assertStatus(401);
});
