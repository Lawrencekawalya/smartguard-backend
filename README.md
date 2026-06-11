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

## API Documentation

All endpoints require `X-SmartGuard-Token` in the header.

### Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/v1/smartguard/telemetry` | Ingest telemetry |
| `GET` | `/api/v1/smartguard/telemetry/latest` | Latest telemetry for device |
| `GET` | `/api/v1/smartguard/dashboard/status` | System status |
| `GET` | `/api/v1/smartguard/dashboard/latest-reading` | Latest reading |
| `GET` | `/api/v1/smartguard/dashboard/latest-fault` | Latest fault |
| `GET` | `/api/v1/smartguard/dashboard/fault-history` | Fault history |
| `GET` | `/api/v1/smartguard/dashboard/relay-history` | Relay history |
| `GET` | `/api/v1/smartguard/dashboard/daily-usage` | Daily energy usage |
| `GET` | `/api/v1/smartguard/dashboard/monthly-usage` | Monthly energy usage |

## Testing

Run tests with:
```bash
php artisan test
```
Current coverage includes schema validation, telemetry ingestion logic, and dashboard data integrity.
