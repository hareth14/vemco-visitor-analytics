<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\SensorStatus;
use Illuminate\Validation\Rules\Enum;

class StoreSensorRequest extends FormRequest
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
            'location_id' => ['required', 'exists:locations,id'],
            'name'        => [
                'required',
                'string',
                Rule::unique('sensors')->where(
                    fn ($query) => $query->where('location_id', $this->input('location_id'))
                ),
            ],
            'status' => ['required', new Enum(SensorStatus::class)],
        ];
    }
}
