SmartGuard Backend Development Task Blueprint
Project Overview

Build the SmartGuard IoT Monitoring Platform using:

Laravel 12
Vue 3 + Inertia.js
MySQL
REST API Architecture
Token-Protected Device Telemetry Ingestion
Real-Time Dashboard Ready Design
Flutter Mobile App Integration

The system receives telemetry every 2 seconds from SmartGuard edge devices (ESP8266/ESP32), stores historical readings, tracks faults, manages relay state transitions, and exposes data to both a Vue dashboard and Flutter mobile application.

Phase 1: Database Architecture [COMPLETED]
Task 1.1: Create Devices Module [DONE]
Model
App\Models\Device
Migration
create_devices_table
Columns
Column	Type
id	BIGINT PK
device_name	string
device_code	string unique
location	string nullable
status	string default active
firmware_version	string nullable
ip_address	string nullable
last_seen_at	timestamp nullable
created_at	timestamp
updated_at	timestamp

Example device code:

SmartGuard-MTR-001
Task 1.2: Create Device Readings Module [DONE]
Model
App\Models\DeviceReading
Migration
create_device_readings_table
Columns
Column	Type
id	BIGINT PK
device_id	FK
voltage	decimal(8,2)
current	decimal(8,3)
real_power	decimal(10,2)
apparent_power	decimal(10,2)
power_factor	decimal(4,2)
energy_kwh	decimal(15,6)
relay_status	boolean
fault_status	string
created_at	indexed timestamp
Performance Requirements

Add indexes:

$table->index('device_id');
$table->index('created_at');
$table->index(['device_id', 'created_at']);

This table will eventually contain millions of records.

Task 1.3: Create Faults Module [DONE]
Model
App\Models\Fault
Migration
create_faults_table
Columns
Column	Type
id	BIGINT PK
device_id	FK
fault_type	string
description	text nullable
occurred_at	timestamp
resolved_at	timestamp nullable
created_at	timestamp
updated_at	timestamp

Examples:

OVERVOLTAGE SURGE
OVERCURRENT DETECTED
UNDERVOLTAGE BROWNOUT
POWER LOSS
Task 1.4: Create Relay Logs Module [DONE]
Model
App\Models\RelayLog
Migration
create_relay_logs_table
Columns
Column	Type
id	BIGINT PK
device_id	FK
action	string
triggered_by	string
created_at	timestamp

Examples:

ON
OFF
AUTO_TRIP
MANUAL_RESET
Task 1.5: Create Energy Summary Module [DONE]
Model
App\Models\EnergySummary
Migration
create_energy_summaries_table
Columns
Column	Type
id	BIGINT PK
device_id	FK
summary_date	date
daily_kwh	decimal(15,6)
monthly_kwh	decimal(15,6)
created_at	timestamp
updated_at	timestamp

Purpose:

Avoid expensive dashboard calculations on massive telemetry tables.

Phase 2: Model Relationships [COMPLETED]
Device
hasMany(DeviceReading::class)
hasMany(Fault::class)
hasMany(RelayLog::class)
hasMany(EnergySummary::class)
DeviceReading
belongsTo(Device::class)
Fault
belongsTo(Device::class)
RelayLog
belongsTo(Device::class)
EnergySummary
belongsTo(Device::class)

Ensure all models contain:

protected $fillable = [...];

Run migrations only after all schemas are finalized. [DONE]

Phase 3: Secure Telemetry API [COMPLETED]
Routes [DONE]

Create:

Route::prefix('v1/smartguard')->group(function () {
    Route::post('/telemetry', [TelemetryController::class, 'store']);
    Route::get('/telemetry/latest', [TelemetryController::class, 'latest']);
});
Telemetry Controller [DONE]

Create:

App\Http\Controllers\Api\TelemetryController
Security Layer [DONE]

Verify:

X-SmartGuard-Token

Against:

SMARTGUARD_API_TOKEN=

Invalid token:

{
    "message": "Unauthorized"
}

Return:

401 Unauthorized
Payload Validation [DONE]

Required:

{
  "device_code": "SmartGuard-MTR-001",
  "status": "RUN",
  "fault_reason": "NONE",
  "voltage": 238.6,
  "current": 5.231,
  "real_power": 1180,
  "apparent_power": 1235,
  "power_factor": 0.95,
  "energy_kwh": 1542.255621,
  "relay_status": 1
}

Validation:

device_code => required|string
status => required|string
fault_reason => required|string

voltage => required|numeric
current => required|numeric
real_power => required|numeric
apparent_power => required|numeric
power_factor => required|numeric
energy_kwh => required|numeric

relay_status => required|integer|in:0,1
Device Auto Registration [DONE]

Use:

Device::firstOrCreate()

If missing:

SmartGuard Unit 1

as fallback name.

Relay Transition Detection [DONE]

Compare previous reading.

Example:

1 -> 0

Create relay log:

AUTO_TRIP

Triggered by:

HARDWARE_ENGINE
Fault Lifecycle Management [DONE]

If:

status = TRIP

Create fault record if no unresolved matching fault exists.

If:

status = RUN

Resolve all open incidents:

resolved_at = now()
Persistence [DONE]

Store telemetry.

Update:

devices.last_seen_at

Return:

201 Created
Phase 4: Dashboard API Endpoints [COMPLETED]

Create endpoints for: [DONE]

Latest Device Reading
Latest Fault
Fault History
Relay History
Daily Energy Usage
Monthly Energy Usage
Device Status

Responses should be optimized for dashboard consumption. [DONE]

Phase 5: Vue Dashboard

Create:

resources/js/Pages/Dashboard.vue
Live Status Banner

Normal:

SYSTEM STABLE / LOAD ONLINE

Display:

Green
Visible at top

Fault:

CRITICAL ALERT: [FAULT_REASON] UNRESOLVED - LOAD ISOLATED

Display:

Flashing red
Highest visual priority
Telemetry Cards

Display:

Voltage (Vrms)
Current (Irms)
Real Power (W)
Apparent Power (VA)
Power Factor
Relay Status
Fault Status
Energy (kWh)
Fault History Table

Columns:

Fault Type
Occurred At
Resolved At
Status
Relay History Table

Columns:

Action
Triggered By
Timestamp
Phase 6: Live Data Polling

Implement Vue polling:

setInterval(...)

Every:

2000ms

Endpoint:

/api/v1/smartguard/telemetry/latest

Update cards without page refresh.

Prevent memory leaks by clearing intervals on component unmount.

Phase 7: Flutter Integration Readiness

The backend must expose clean REST endpoints for:

Login
Device List
Latest Telemetry
Fault History
Relay History
Daily Usage
Monthly Usage

Use Laravel Sanctum for API authentication.

Phase 8: Production Readiness

Implement:

Form Requests
API Resources
Service Layer
Repository Layer
Policies and Authorization
Queue Support
Database Seeders
Feature Tests (Pest)
API Versioning
Structured Logging

Code must be maintainable, testable, and scalable for continuous telemetry ingestion every 2 seconds from multiple SmartGuard devices.