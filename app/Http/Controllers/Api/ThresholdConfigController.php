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
            'threshold_config_ack_payload' => $device->threshold_config_ack_payload,
            'threshold_config_status' => $device->threshold_config_status,
            'threshold_config_error' => $device->threshold_config_error,
            'threshold_config_synced_at' => $device->threshold_config_synced_at?->toIso8601String(),
        ]);
    }

    public function status(Request $request)
    {
        $validated = $request->validate([
            'device_code' => 'required|string',
            'version' => 'required|integer|min:0',
            'max_current' => 'required|numeric|min:0|max:20',
            'min_voltage' => 'required|numeric|min:0|max:258',
            'max_voltage' => 'required|numeric|min:0|max:258',
            'min_power_factor' => 'required|numeric|min:0|max:1',
            'max_real_power' => 'required|numeric|min:0|max:5000',
            'max_apparent_power' => 'required|numeric|min:0|max:5000',
        ]);

        $device = Device::firstOrCreate(
            ['device_code' => $validated['device_code']],
            ['device_name' => 'SmartGuard Unit '.(Device::count() + 1)]
        );

        $device = $this->thresholdConfigService->recordBoardStatus($device, [
            'version' => (int) $validated['version'],
            'max_current' => (float) $validated['max_current'],
            'min_voltage' => (float) $validated['min_voltage'],
            'max_voltage' => (float) $validated['max_voltage'],
            'min_power_factor' => (float) $validated['min_power_factor'],
            'max_real_power' => (float) $validated['max_real_power'],
            'max_apparent_power' => (float) $validated['max_apparent_power'],
        ]);

        return response()->json([
            'device_code' => $device->device_code,
            'threshold_config_ack_version' => $device->threshold_config_ack_version,
            'threshold_config_ack_payload' => $device->threshold_config_ack_payload,
            'threshold_config_status' => $device->threshold_config_status,
            'threshold_config_synced_at' => $device->threshold_config_synced_at?->toIso8601String(),
        ]);
    }
}
