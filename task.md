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

Phase 5: SmartGuard Dashboard UI [COMPLETED]

Create: [DONE]

resources/js/Pages/Dashboard.vue
Live Status Banner [DONE]

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
Telemetry Cards [DONE]

Display:

Voltage (Vrms)
Current (Irms)
Real Power (W)
Apparent Power (VA)
Power Factor
Relay Status
Fault Status
Energy (kWh)
Fault History Table [DONE]

Columns:

Fault Type
Occurred At
Resolved At
Status
Relay History Table [DONE]

Columns:

Action
Triggered By
Timestamp

Phase 5.1: Telemetry Trend Charts [COMPLETED]

Backend: [DONE]

GET /api/v1/smartguard/dashboard/voltage-trend
GET /api/v1/smartguard/dashboard/current-trend
GET /api/v1/smartguard/dashboard/power-trend

Frontend: [DONE]

Voltage Trend (Vrms)
AC Current Trend (Irms)
Power Consumption Trend (W)
Smooth line curves
Responsive sizing
Tooltips and Grid lines

Phase 6: Live Data Polling [COMPLETED]

Implement Vue polling: [DONE]

setInterval(...)

Every:

2000ms

Endpoint: [DONE]

/api/v1/smartguard/telemetry/latest

Update cards without page refresh. [DONE]

Prevent memory leaks by clearing intervals on component unmount. [DONE]

Phase 6.1: Device Management & Fault Configuration [COMPLETED]

Backend: [DONE]
- Device CRUD API endpoints (`/api/v1/devices`).
- Fault Settings management API (`/api/v1/fault-settings`).
- Dynamic fault detection in `TelemetryService` using database-driven thresholds.
- Automatic fault resolution logic when parameters return to normal.

Frontend: [DONE]
- Device management dashboard (Index, Create, Edit).
- Fault configuration panel (FaultThresholds.vue).
- Real-time device status and latest reading integration.

Phase 6.3: Energy Analytics & Consumption Reporting [COMPLETED]

Backend: [DONE]
- `EnergyAnalyticsService` for multi-device data aggregation.
- Database-driven tariff management (`energy_settings` table).
- Validated API endpoints for energy summary and daily/weekly/monthly trends.
- Historical consumption reporting API with peak power and fault counts.
- Dynamic cost estimation using `energy_kwh × tariff_rate`.
- CSV and PDF report exports that respect the selected date range.
- API Resources and Form Requests for stable responses and input validation.

Frontend: [DONE]
- Dedicated Energy Analytics dashboard accessible from sidebar.
- Summary cards for today, week, month, and estimated cost.
- Responsive, dark-compatible ApexCharts for the last 30 days, 12 weeks, and 12 months.
- Cost analysis table with database-driven tariff and currency formatting.
- Daily management report with consumption, cost, peak power, and fault count.
- CSV/PDF export controls that preserve active date filters.
- Separate Settings > Energy Settings page for tariff, currency, and description.
- Live daily and monthly summary aggregation during telemetry ingestion.
- Ten-second background refresh while the Energy Analytics page is open.
- `energy:rebuild-summaries` command for historical telemetry reconciliation.

Verification: [DONE]
- Phase 6.3 feature tests cover summaries, reports, calculations, exports, validation, authentication, and settings.
- Production frontend build completed successfully.

Phase 7: Flutter Integration & Persistent Fault Alarm System [PLANNED]

Goal

Prepare a stable, authenticated mobile API and a real-time fault notification
pipeline for the Flutter application.

When SmartGuard opens a fault, the backend must send a push event with the
shortest practical latency so the phone can start a prominent local alarm. The
alarm remains active while at least one fault is unresolved and stops only after
the backend confirms that all relevant faults have been resolved.

Architecture Principle

- Laravel is the source of truth for fault state.
- Firebase Cloud Messaging (FCM) is the wake-up and event-delivery channel.
- REST synchronization guarantees recovery from delayed, duplicated, missed,
  or out-of-order push messages.
- Flutter owns local alarm playback because a server push cannot continuously
  play sound by itself.
- Do not send notifications for every two-second telemetry reading. Send events
  only when a fault opens, resolves, or requires a configured reminder.

Task 7.1: Mobile Authentication with Laravel Sanctum

Backend:

- Add `HasApiTokens` to the `User` model.
- Create mobile authentication endpoints:
  - `POST /api/v1/mobile/auth/login`
  - `GET /api/v1/mobile/auth/user`
  - `POST /api/v1/mobile/auth/logout`
  - `POST /api/v1/mobile/auth/logout-all`
- Login accepts email, password, and a human-readable device name.
- Return a Sanctum bearer token and authenticated user resource.
- Protect every mobile endpoint with `auth:sanctum`.
- Add login rate limiting, validation, token expiration policy, token rotation
  guidance, and token revocation on logout.
- Never expose `X-SmartGuard-Token` to the Flutter application; that credential
  remains exclusive to trusted SmartGuard hardware.

Task 7.2: Versioned Mobile REST API

Create consistent JSON resources, pagination, validation, and ISO 8601 UTC
timestamps for:

- `GET /api/v1/mobile/devices`
- `GET /api/v1/mobile/devices/{device}`
- `GET /api/v1/mobile/devices/{device}/latest-telemetry`
- `GET /api/v1/mobile/devices/{device}/faults`
- `GET /api/v1/mobile/devices/{device}/relay-history`
- `GET /api/v1/mobile/devices/{device}/energy/daily`
- `GET /api/v1/mobile/devices/{device}/energy/monthly`
- `GET /api/v1/mobile/dashboard`

Requirements:

- Reuse the existing service layer and API Resources where appropriate.
- Add filters for fault status, date range, and pagination.
- Include device online/offline state derived from `last_seen_at`.
- Include unresolved fault count and current relay/fault state in device
  summaries so the app can render the same information as the web dashboard.
- Apply authorization policies so users can only access devices assigned to
  them. Define the user-to-device assignment model before exposing production
  mobile access.

Task 7.3: Mobile Push Token Registration

Create a `mobile_devices` table and model with:

- `id`
- `user_id`
- `installation_id` (unique app-install identifier)
- `fcm_token` (encrypted or otherwise protected at rest)
- `fcm_token_hash` (unique lookup/deduplication value)
- `platform` (`android` or `ios`)
- `device_name`
- `app_version` nullable
- `notifications_enabled`
- `last_seen_at`
- timestamps

Create endpoints:

- `POST /api/v1/mobile/push-tokens` to register or refresh an installation.
- `DELETE /api/v1/mobile/push-tokens/{installation_id}` on logout or explicit
  deregistration.

Requirements:

- Upsert token records because FCM tokens can rotate.
- Reassign a token safely when the authenticated installation changes account.
- Remove or disable invalid tokens after a permanent FCM delivery failure.
- Allow one user to register multiple phones.

Task 7.4: Fault Domain Events

Refactor fault lifecycle handling so state transitions produce explicit events:

- `FaultDetected` when a new unresolved fault record is created.
- `FaultResolved` when an unresolved fault receives `resolved_at`.

Requirements:

- Dispatch events only after the telemetry database transaction commits.
- Never emit another `FaultDetected` event for an already-open matching fault.
- Emit resolution events for every fault that actually changed state.
- Include a unique event ID for idempotent mobile processing.
- Keep telemetry ingestion fast; notification network calls must not run inside
  `TelemetryService` or its database transaction.

Task 7.5: Queued Firebase Cloud Messaging Delivery

Implement an FCM HTTP v1 notification service and queued jobs:

- `SendFaultDetectedNotification`
- `SendFaultResolvedNotification`
- Optional `SendUnresolvedFaultReminder`

Use service-account credentials through environment configuration and never
commit credentials to source control.

Fault-open push payload:

- `event_id`
- `event_type = fault.opened`
- `alarm_action = START`
- `fault_id`
- `fault_type`
- `severity`
- `device_id`
- `device_code`
- `device_name`
- `occurred_at`

Fault-resolved push payload:

- `event_id`
- `event_type = fault.resolved`
- `alarm_action = SYNC`
- `fault_id`
- `device_id`
- `device_code`
- `resolved_at`

Requirements:

- Use high-priority Android delivery and the corresponding iOS alert settings.
- Configure retry/backoff and structured delivery logging.
- Make jobs idempotent to avoid duplicate notification records.
- Store notification delivery attempts/status for audit and troubleshooting.
- A resolution event uses `SYNC`, not an unconditional stop, because another
  unresolved fault may still require the alarm.
- Queue workers must be supervised in production.

Task 7.6: Authoritative Active Alarm API

Create:

- `GET /api/v1/mobile/alarms/active`

Response must include:

- `alarm_active`
- all unresolved faults accessible to the authenticated user
- affected devices
- highest severity
- `server_time`
- a monotonically increasing state version or last-change timestamp

Optional acknowledgement endpoint:

- `POST /api/v1/mobile/faults/{fault}/acknowledge`

Acknowledgement records that a user saw the alert but does not resolve the fault
and does not permanently stop the required alarm.

Synchronization rules:

- Flutter calls the active alarm endpoint after login, app launch, app resume,
  push receipt, and network reconnection.
- Flutter starts/continues the alarm when `alarm_active` is true.
- Flutter stops the alarm only when a fresh authenticated response confirms
  `alarm_active` is false.
- The app must deduplicate events by `event_id` and tolerate out-of-order pushes.
- Add a short foreground polling fallback while an alarm is active; FCM is not
  treated as guaranteed delivery.

Task 7.7: Flutter Persistent Alarm Behavior

Flutter implementation requirements:

- Use Firebase Messaging for foreground, background, and terminated-app push
  handling.
- Use local notifications with a dedicated high-importance fault alarm channel.
- Display the device, fault type, occurrence time, current state, and a direct
  action that opens the affected device/fault screen.
- Maintain a local alarm state machine backed by the active alarm API.
- In the foreground, loop the alarm audio until backend synchronization reports
  no unresolved faults.
- Persist enough state locally to restore the alarm UI after app restart.
- Re-register the FCM token whenever Firebase rotates it.
- Request notification/alarm permissions with a clear user-facing explanation.
- Show an in-app warning when notifications, alarm permissions, battery policy,
  or background delivery settings prevent reliable alerts.

Android:

- Create a maximum/high-importance notification channel with alarm audio,
  vibration, lock-screen visibility, and an ongoing alarm notification.
- Use an Android foreground service for continuous playback while the alarm is
  active, subject to Android permission and device-policy requirements.
- Consider full-screen alarm UI only for genuine critical faults and only where
  Android permits it.
- Stop the foreground service only after authoritative resolution
  synchronization.

iOS:

- Implement normal APNs/FCM alerts and foreground alarm playback.
- Apply for Apple's Critical Alerts entitlement if uninterrupted safety alerts
  are a product requirement.
- Document that iOS does not guarantee indefinite custom audio while the app is
  backgrounded or terminated without the required entitlement/system support.
- Use repeated unresolved-fault reminders and state reconciliation as the
  fallback; do not claim that ordinary iOS notifications can ring forever.

Task 7.8: Reliability, Escalation, and Recovery

- Add a configurable reminder interval for unresolved critical faults.
- Cancel future reminders when all relevant faults resolve.
- On queue or FCM outage, retain retryable jobs and expose delivery failures in
  logs/monitoring.
- On app reconnection, recover current alarm state entirely from the REST API.
- Handle multiple simultaneous faults without starting competing audio loops.
- Handle one fault resolving while another remains open.
- Define severity mapping and which fault types trigger audible alarms.
- Define optional escalation channels such as SMS/email for future work; these
  do not replace push plus REST synchronization.

Task 7.9: Security and Privacy

- Authorize users against assigned devices for REST data and push delivery.
- Do not place secrets, raw telemetry history, or sensitive personal data in
  push payloads.
- Validate all device, fault, and installation identifiers.
- Revoke mobile tokens and push registrations on logout/account disablement.
- Rate limit authentication, token registration, and acknowledgement endpoints.
- Audit token registration, fault delivery, acknowledgement, and resolution.

Task 7.10: Tests and Acceptance Criteria

Backend feature/unit tests:

- Mobile login succeeds with valid credentials and fails safely otherwise.
- Logout revokes the current Sanctum token.
- Protected mobile endpoints reject unauthenticated requests.
- Users cannot access or receive alerts for unassigned devices.
- Push token registration is idempotent and handles token rotation.
- One new fault creates exactly one `FaultDetected` event and queued push job.
- Repeated fault telemetry does not create duplicate open-fault notifications.
- Resolving a fault creates a resolution event only after commit.
- FCM failures retry and permanent invalid-token failures disable the token.
- Active alarm endpoint remains true until the final unresolved fault resolves.
- Concurrent telemetry requests do not duplicate fault events.
- Existing telemetry ingestion and dashboard tests continue to pass.

Flutter acceptance tests:

- Foreground fault starts the alarm and displays the correct fault.
- Background/terminated push opens the critical local notification.
- App launch with a missed push still starts the alarm after REST sync.
- Duplicate/out-of-order events do not create duplicate alarms.
- Resolving one of multiple faults does not stop the alarm.
- Resolving the final fault stops playback and updates the UI.
- Token refresh and logout correctly update backend registration.
- Denied permissions produce a visible reliability warning.

Completion Criteria

- The Flutter app can display all existing dashboard data through
  Sanctum-protected mobile endpoints.
- A newly opened fault queues a push notification without delaying telemetry
  ingestion.
- Android can maintain a local audible alarm while a fault remains unresolved,
  within granted OS permissions and device policy.
- iOS behavior and Critical Alerts entitlement limitations are explicitly
  documented and tested.
- Missed push messages cannot leave the app in a permanently incorrect state
  because startup/resume/reconnect synchronization restores backend truth.
- The alarm stops only after the active alarm API confirms that no applicable
  unresolved fault remains.

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
