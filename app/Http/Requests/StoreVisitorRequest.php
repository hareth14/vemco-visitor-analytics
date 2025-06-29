<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SensorBelongsToLocation;

class StoreVisitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
            'sensor_id'   => ['required', 'exists:sensors,id', new SensorBelongsToLocation($this->input('location_id'))],
            'date'        => ['required', 'date_format:Y-m-d'],
            'count'       => ['required', 'integer', 'min:0'],
        ];
    }
}
