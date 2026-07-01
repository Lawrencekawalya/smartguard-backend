<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
{
    public function __construct(
        protected DeviceService $deviceService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Device::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('device_name', 'like', "%{$search}%")
                ->orWhere('device_code', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        }

        $devices = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Devices/Index', [
            'devices' => $devices,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Devices/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'device_code' => 'required|string|max:255|unique:devices,device_code',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
            'firmware_version' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
        ]);

        $this->deviceService->createDevice($validated);

        return redirect()->route('devices.index')->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device): Response
    {
        return Inertia::render('Devices/Show', [
            'device' => $device->load(['readings' => function ($query) {
                $query->latest()->limit(1);
            }, 'faults' => function ($query) {
                $query->latest()->limit(5);
            }, 'relayLogs' => function ($query) {
                $query->latest()->limit(5);
            }]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device): Response
    {
        return Inertia::render('Devices/Edit', [
            'device' => $device,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'device_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('devices', 'device_code')->ignore($device->id),
            ],
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
            'firmware_version' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
        ]);

        $this->deviceService->updateDevice($device, $validated);

        return redirect()->route('devices.index')->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $this->deviceService->deleteDevice($device);

        return redirect()->route('devices.index')->with('success', 'Device deleted successfully.');
    }
}
