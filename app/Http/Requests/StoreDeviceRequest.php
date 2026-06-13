<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'device_name' => 'required|string|max:255',
            'device_code' => 'required|string|max:255|unique:devices,device_code',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
            'firmware_version' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
        ];
    }
}
