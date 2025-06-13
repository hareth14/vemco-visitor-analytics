<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Sensor;

/**
 * Validation rule to ensure a sensor belongs to the specified location.
 */

class SensorBelongsToLocation implements ValidationRule
{
    protected $locationId;

    public function __construct($locationId)
    {
        $this->locationId = $locationId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sensor = Sensor::find($value);

        if (! $sensor || $sensor->location_id != $this->locationId) {
            $fail('The specified sensor does not belong to the given location.');
        }
    }
}