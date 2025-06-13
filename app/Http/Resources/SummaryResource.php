<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SummaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'total_visitors_last_7_days' => $this['total_visitors_last_7_days'],
            'active_sensors' => $this['active_sensors'],
            'inactive_sensors' => $this['inactive_sensors'],
        ];
    }
}
