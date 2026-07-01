<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\ThresholdConfigService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThresholdConfigController extends Controller
{
    public function __construct(
        private readonly ThresholdConfigService $thresholdConfigService
    ) {}

    public function show(Request $request)
    {
        $validated = $request->validate([
            'device_code' => 'required|string',
        ]);

        $device = Device::firstOrCreate(
            ['device_code' => $validated['device_code']],
            ['device_name' => 'SmartGuard Unit '.(Device::count() + 1)]
        );

        return response($this->thresholdConfigService->getConfigFrame($device), Response::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    public function ack(Request $request)
    {
        $validated = $request->validate([
            'device_code' => 'required|string',
            'version' => 'required|integer|min:1',
            'status' => 'required|string|in:ACK,ERR',
            'message' => 'nullable|string|max:255',
        ]);

        $device = Device::where('device_code', $validated['device_code'])->first();

        if (! $device) {
            return response()->json(['message' => 'Device not found'], Response::HTTP_NOT_FOUND);
        }

        $device = $this->thresholdConfigService->recordAck(
            $device,
            (int) $validated['version'],
            $validated['status'],
            $validated['message'] ?? null
        );

        return response()->json([
            'device_code' => $device->device_code,
            'threshold_config_version' => $device->threshold_config_version,
            'threshold_config_ack_version' => $device->threshold_config_ack_version,
            'threshold_config_status' => $device->threshold_config_status,
            'threshold_config_error' => $device->threshold_config_error,
            'threshold_config_synced_at' => $device->threshold_config_synced_at?->toIso8601String(),
        ]);
    }
}
