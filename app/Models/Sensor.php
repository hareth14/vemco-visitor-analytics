<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\SensorStatus;
use Illuminate\Database\Eloquent\Builder;

class Sensor extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'status', 'location_id'];
    protected $casts = [
        'status' => SensorStatus::class,
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }
    
    public function scopeStatus(Builder $query, ?SensorStatus $statusEnum): Builder
    {
        if ($statusEnum) {
            return $query->where('status', $statusEnum->value);
        }

        return $query;
    }
}
