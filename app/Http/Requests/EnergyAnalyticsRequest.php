<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnergyAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function dateRange(): array
    {
        return [
            $this->validated('start_date'),
            $this->validated('end_date'),
        ];
    }
}
