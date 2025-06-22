<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'location_id' => $this->location_id,
            'sensor_id'   => $this->sensor_id,
            'date'        => $this->date,
            'count'       => $this->count,
            'sensor'      => new SensorResource($this->whenLoaded('sensor')),
        ];
    }
}
