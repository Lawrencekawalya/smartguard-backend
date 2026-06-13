<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaultSettingResource;
use App\Models\FaultSetting;
use App\Services\FaultSettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

        $setting = $this->faultSettingService->updateSetting($faultSetting, $validated);
        return new FaultSettingResource($setting);
    }
}
