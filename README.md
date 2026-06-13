# SmartGuard Backend

IoT Monitoring Platform Backend built with Laravel 12.

## Features Implemented

### Phase 1: Database Architecture
- Core models: `Device`, `DeviceReading`, `Fault`, `RelayLog`, `EnergySummary`.
- Full migration schema with optimized indexes.
- Eloquent relationships established.

### Phase 3: Secure Telemetry API
- `POST /api/v1/smartguard/telemetry`: Secure data ingestion from IoT devices.
- `GET /api/v1/smartguard/telemetry/latest`: Retrieve latest device data.
- Security: `X-SmartGuard-Token` header validation.
- Service layer for device auto-registration, relay transition detection, and fault management.

### Phase 4: Dashboard API Endpoints
- `GET /api/v1/smartguard/dashboard/status`: Real-time device status.
- `GET /api/v1/smartguard/dashboard/latest-reading`: Detailed latest telemetry.
- `GET /api/v1/smartguard/dashboard/latest-fault`: Most recent fault details.
- `GET /api/v1/smartguard/dashboard/fault-history`: Paginated fault logs.
- `GET /api/v1/smartguard/dashboard/relay-history`: Paginated relay state changes.
- `GET /api/v1/smartguard/dashboard/daily-usage`: Daily energy consumption data.
- `GET /api/v1/smartguard/dashboard/monthly-usage`: Monthly energy aggregation.

### Phase 5: SmartGuard Dashboard UI
- Integrated SmartGuard telemetry into the main dashboard.
- Reusable components: `StatusBanner`, `MetricCard`, `FaultHistoryTable`, `RelayHistoryTable`.
- Real-time data visualization (Voltage, Current, Power, Energy, etc.).
- Robust handling of loading, empty, and error states.
- Responsive design following existing project themes.

### Phase 5.1: Telemetry Trend Charts
- `GET /api/v1/smartguard/dashboard/voltage-trend`: Voltage history for charting.
- `GET /api/v1/smartguard/dashboard/current-trend`: Current history for charting.
- `GET /api/v1/smartguard/dashboard/power-trend`: Power history for charting.
- Integrated ApexCharts for smooth, responsive trend visualization.
- Dark theme compatible with tooltips and grid lines.

### Phase 6: Live Dashboard Polling
- Real-time dashboard updates every 2 seconds.
- Automatic refresh of status, metrics, trends, and history.
- Smart resource management with Vue lifecycle hooks.
- Non-blocking error handling and automatic retry.

### Phase 6.1: Device Management & Fault Configuration
- `GET /api/v1/devices`: List all registered devices.
- `POST /api/v1/devices`: Register new SmartGuard hardware.
- `PUT /api/v1/fault-settings/{id}`: Dynamically adjust protection thresholds.
- Backend-driven fault detection for Voltage (Over/Under) and Current (Over).
- Settings dashboard for managing electrical protection limits.
- Full Pest test suite for management APIs and telemetry integration.

### Phase 6.2: Device Management Dashboard
- **Devices List**: Comprehensive overview of all units with search, pagination, and status tracking.
- **Device Provisioning**: Easy interface to register new hardware with location and network details.
- **Device Details**: Deep-dive view showing latest telemetry, recent fault history, and relay state logs for specific units.
- **Integrated Navigation**: "Devices" section added to the main sidebar for seamless platform management.
- **Validation**: Full form validation and error handling for device management actions.

## API Documentation

### SmartGuard Telemetry APIs
Requires `X-SmartGuard-Token` header.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/v1/smartguard/telemetry` | Ingest telemetry |
| `GET` | `/api/v1/smartguard/telemetry/latest` | Latest telemetry for device |

### Management & Settings APIs
Requires Sanctum authentication.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/devices` | List devices |
| `GET` | `/api/v1/devices/{id}` | Device details |
| `POST` | `/api/v1/devices` | Create device |
| `PUT` | `/api/v1/devices/{id}` | Update device |
| `DELETE` | `/api/v1/devices/{id}` | Delete device |
| `GET` | `/api/v1/fault-settings` | List protection limits |
| `PUT` | `/api/v1/fault-settings/{id}` | Update protection limit |

## Testing

Run tests with:
```bash
php artisan test
```
Current coverage includes schema validation, telemetry ingestion logic, and dashboard data integrity.
