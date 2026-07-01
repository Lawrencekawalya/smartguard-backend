<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTelemetryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('energy_kwh') && is_numeric($this->input('energy_kwh'))) {
            $this->merge([
                'energy_kwh' => max(0, (float) $this->input('energy_kwh')),
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_code' => 'required|string',
            'status' => 'required|string',
            'fault_reason' => 'required|string',
            'voltage' => 'required|numeric',
            'current' => 'required|numeric',
            'real_power' => 'required|numeric',
            'apparent_power' => 'required|numeric',
            'power_factor' => 'required|numeric',
            'energy_kwh' => 'required|numeric|min:0',
            'relay_status' => 'required|integer|in:0,1',
        ];
    }
}
