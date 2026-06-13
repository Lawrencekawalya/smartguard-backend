<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DeviceController extends Controller
{
    public function __construct(
        protected DeviceService $deviceService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return DeviceResource::collection($this->deviceService->getAllDevices());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeviceRequest $request): DeviceResource
    {
        $device = $this->deviceService->createDevice($request->validated());
        return new DeviceResource($device);
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device): DeviceResource
    {
        return new DeviceResource($device);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeviceRequest $request, Device $device): DeviceResource
    {
        $device = $this->deviceService->updateDevice($device, $request->validated());
        return new DeviceResource($device);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device): Response
    {
        $this->deviceService->deleteDevice($device);
        return response()->noContent();
    }

    /**
     * Get the latest reading for a device.
     */
    public function latestReading(Device $device)
    {
        $reading = $device->readings()->latest('id')->first();
        return $reading ? response()->json($reading) : response()->json(['message' => 'No readings found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Get faults for a device.
     */
    public function faults(Device $device)
    {
        return response()->json($device->faults()->latest('id')->get());
    }

    /**
     * Get relay logs for a device.
     */
    public function relayLogs(Device $device)
    {
        return response()->json($device->relayLogs()->latest('id')->get());
    }
}
