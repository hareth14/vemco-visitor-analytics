<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $fillable = ['name', 'status', 'location_id'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }
}
