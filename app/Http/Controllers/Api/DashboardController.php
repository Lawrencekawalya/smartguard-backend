<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardStatusResource;
use App\Http\Resources\EnergyUsageResource;
use App\Http\Resources\FaultResource;
use App\Http\Resources\RelayLogResource;
use App\Http\Resources\TelemetryResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    private function getDeviceCode(Request $request): string
    {
        return $request->query('device_code', '');
    }

    public function status(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $device = $this->dashboardService->getStatus($deviceCode);
        if (! $device) {
            return response()->json(['message' => 'Device not found'], Response::HTTP_NOT_FOUND);
        }

        return new DashboardStatusResource($device);
    }

    public function latestReading(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $reading = $this->dashboardService->getLatestReading($deviceCode);
        if (! $reading) {
            return response()->json(['message' => 'No readings found'], Response::HTTP_NOT_FOUND);
        }

        return new TelemetryResource($reading);
    }

    public function latestFault(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $fault = $this->dashboardService->getLatestFault($deviceCode);
        if (! $fault) {
            return response()->json(['message' => 'No faults found'], Response::HTTP_NOT_FOUND);
        }

        return new FaultResource($fault);
    }

    public function faultHistory(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $limit = $request->query('limit', 20);
        $faults = $this->dashboardService->getFaultHistory($deviceCode, (int) $limit);

        return FaultResource::collection($faults);
    }

    public function relayHistory(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $limit = $request->query('limit', 20);
        $logs = $this->dashboardService->getRelayHistory($deviceCode, (int) $limit);

        return RelayLogResource::collection($logs);
    }

    public function dailyUsage(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $usage = $this->dashboardService->getDailyUsage($deviceCode);

        return EnergyUsageResource::collection($usage);
    }

    public function monthlyUsage(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $usage = $this->dashboardService->getMonthlyUsage($deviceCode);

        return EnergyUsageResource::collection($usage);
    }

    public function voltageTrend(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $trend = $this->dashboardService->getVoltageTrend($deviceCode);

        return response()->json($trend);
    }

    public function currentTrend(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $trend = $this->dashboardService->getCurrentTrend($deviceCode);

        return response()->json($trend);
    }

    public function powerTrend(Request $request)
    {
        $deviceCode = $this->getDeviceCode($request);
        if (! $deviceCode) {
            return response()->json(['message' => 'Device code is required'], Response::HTTP_BAD_REQUEST);
        }

        $trend = $this->dashboardService->getPowerTrend($deviceCode);

        return response()->json($trend);
    }
}
