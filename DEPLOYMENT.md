# SmartGuard Laravel Deployment

## Server Requirements

- PHP 8.3+
- Composer 2
- Node.js 22+ and npm
- MySQL or MariaDB
- Web server document root pointed to `public/`
- HTTPS certificate

## Production Environment

Create the production `.env` on the server from `.env.example`, then set these values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-smartguard-domain.example
ASSET_URL=

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartguard
DB_USERNAME=smartguard
DB_PASSWORD=replace-with-strong-password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
SANCTUM_STATEFUL_DOMAINS=your-smartguard-domain.example
SESSION_DOMAIN=.your-smartguard-domain.example

SMARTGUARD_API_TOKEN=replace-with-long-random-token
SMARTGUARD_OFFLINE_AFTER_SECONDS=8

INERTIA_SSR_ENABLED=false
```

The same `SMARTGUARD_API_TOKEN` must be configured in the NodeMCU firmware and the mobile app.

## First Deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=FaultSettingSeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Create the first admin user using the app registration flow, then disable public registration if required by the final deployment policy.

## Queue Worker

Run the database queue worker with a process manager such as Supervisor or systemd:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=60
```

## Web Server

Point the web server to:

```text
/path/to/smartguard-backend/public
```

Do not point the web server to the project root.

## Device And Mobile Updates

After hosting, update:

- NodeMCU `serverEndpoint`
- NodeMCU `configEndpoint`
- NodeMCU `configAckEndpoint`
- NodeMCU `configStatusEndpoint`
- NodeMCU `apiToken`
- Flutter `backendBaseUrl`
- Flutter API token if it is compiled into the app

Expected hosted device API endpoints:

```text
POST https://your-smartguard-domain.example/api/v1/smartguard/telemetry
GET  https://your-smartguard-domain.example/api/v1/smartguard/config?device_code=SmartGuard-MTR-001
POST https://your-smartguard-domain.example/api/v1/smartguard/config/ack
POST https://your-smartguard-domain.example/api/v1/smartguard/config/status
GET  https://your-smartguard-domain.example/api/v1/mobile/devices/SmartGuard-MTR-001/alarm-state
```

## Release Checklist

- `composer test`
- `npm run build`
- `.env` uses production URL, database, and token
- HTTPS works
- NodeMCU posts telemetry to hosted `/api/v1/smartguard/telemetry`
- Fault threshold config sync shows `Board synced`
- Mobile app alarm state reaches hosted `/api/v1/mobile/.../alarm-state`
