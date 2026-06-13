<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEnergySettingRequest;
use App\Http\Resources\EnergySettingResource;
use App\Services\EnergyAnalyticsService;
use Illuminate\Http\JsonResponse;

class EnergySettingController extends Controller
{
    public function __construct(private readonly EnergyAnalyticsService $energyService) {}

    public function index(): JsonResponse
    {
        return response()->json(
            (new EnergySettingResource($this->energyService->getSetting()))->resolve()
        );
    }

    public function update(UpdateEnergySettingRequest $request): JsonResponse
    {
        $setting = $this->energyService->getSetting();
        $setting->update($request->validated());

        return response()->json(
            (new EnergySettingResource($setting->refresh()))->resolve($request)
        );
    }
}
