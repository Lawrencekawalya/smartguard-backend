<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTelemetryRequest;
use App\Http\Resources\TelemetryResource;
use App\Services\TelemetryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelemetryController extends Controller
{
    public function __construct(
        protected TelemetryService $telemetryService
    ) {}

    /**
     * Store a new telemetry reading.
     */
    public function store(StoreTelemetryRequest $request)
    {
        // Delegate to service
        $reading = $this->telemetryService->storeTelemetry($request->validated());

        // Return resource
        return new TelemetryResource($reading);
    }

    /**
     * Get the latest telemetry for a device.
     */
    public function latest(Request $request)
    {
        $deviceCode = $request->query('device_code');
        if (!$deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $reading = $this->telemetryService->getLatestTelemetry($deviceCode);

        if (!$reading) {
            return response()->json(['message' => 'No telemetry found for this device'], Response::HTTP_NOT_FOUND);
        }

        return new TelemetryResource($reading);
    }
}
