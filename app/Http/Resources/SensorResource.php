<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SensorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'status'   => $this->status,
            'location_id' => $this->location_id,
            'location' => [
                'id'   => $this->location->id,
                'name' => $this->location->name,
            ],
        ];
    }
}
