<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
      'accommodation_id',
      'price',
      'offer_price',
      'area',
      'people_count',
      'description',
      'room_number'
    ];

    public function Accommodation(){
      return $this->belongsToMany(Accommodation::class);
    }

    public function Room_picture(){
      return $this->hasMany(Room_picture::class);
    }

    public function User()
    {
      return $this->belongsToMany(User::class);
    }
}
