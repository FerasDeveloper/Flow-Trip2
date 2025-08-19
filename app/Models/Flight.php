<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;
    protected $fillable = [
      'air_line_id',
      'plane_id',
      'price',
      'offer_price',
      'flight_number',
      'starting_point_location',
      'landing_point_location',
      'starting_airport',
      'landing_airport',
      'start_time',
      'land_time',
      'estimated_time',
      'date'
    ];

    public function Air_line(){
      return $this->belongsTo(Air_line::class);
    }
    public function Plane(){
      return $this->belongsTo(Plane::class);
    }
    public function User()
    {
      return $this->belongsToMany(User::class);
    }

    public function Seat(){
      return $this->hasMany(Seat::class);
    }
}
