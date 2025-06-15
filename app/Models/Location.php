<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;
    
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