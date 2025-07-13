<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_room extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id',
    'room_id',
    'traveler_name',
    'national_number',
    'start_date',
    'end_date'
  ];
}
