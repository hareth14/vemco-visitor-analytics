<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sensor extends Model
{
    use HasFactory;
    
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
