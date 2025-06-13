<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name'];

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }
}