<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_flight extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id',
    'flight_id',
    'traveler_name',
    'national_number',
    'seat_number',
    'price'
  ];
}
