<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function car_type(){
      return $this->belongsTo(Car_type::class);
    }
    public function vehicle_owner(){
      return $this->belongsTo(Vehicle_owner::class);
    }
    public function car_picture(){
      return $this->hasMany(Car_picture::class);
    }
}
