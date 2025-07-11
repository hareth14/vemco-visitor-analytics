<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visitor extends Model
{
    use HasFactory;
    
    protected $fillable = ['location_id', 'sensor_id', 'date', 'count'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }
}
