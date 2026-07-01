<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaultSettingResource;
use App\Models\FaultSetting;
use App\Services\FaultSettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class FaultSettingController extends Controller
{
    public function __construct(
        protected FaultSettingService $faultSettingService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return FaultSettingResource::collection($this->faultSettingService->getAllSettings());
    }

    /**
     * Display the specified resource.
     */
    public function show(FaultSetting $faultSetting): FaultSettingResource
    {
        return new FaultSettingResource($faultSetting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FaultSetting $faultSetting): FaultSettingResource
    {
        $validated = $request->validate([
            'min_value' => 'sometimes|required|numeric',
            'max_value' => 'sometimes|required|numeric',
            'enabled' => 'sometimes|required|boolean',
            'description' => 'nullable|string',
        ]);

        $this->validateHardwareSafeLimits($faultSetting, $validated);

        $setting = $this->faultSettingService->updateSetting($faultSetting, $validated);

        return new FaultSettingResource($setting);
    }

    private function validateHardwareSafeLimits(FaultSetting $setting, array $data): void
    {
        $minValue = (float) ($data['min_value'] ?? $setting->min_value);
        $maxValue = (float) ($data['max_value'] ?? $setting->max_value);

        $isInvalid = match ($setting->parameter) {
            'voltage' => $minValue < 185 || $maxValue > 258 || $minValue >= $maxValue,
            'current' => $maxValue < 0.1 || $maxValue > 20,
            'power_factor' => $minValue < 0 || $minValue > 1,
            'real_power', 'apparent_power' => $maxValue < 0 || $maxValue > 5000,
            default => false,
        };

        if ($isInvalid) {
            throw ValidationException::withMessages([
                'max_value' => 'Threshold is outside the hardware-safe SmartGuard range.',
            ]);
        }
    }
}
