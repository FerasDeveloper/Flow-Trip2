<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room_picture extends Model
{
    use HasFactory;
    protected $fillable = [
      'room_id',
      'room_picture'
    ];

    public function Room(){
      return $this->belongsToMany(Room::class);
    }
}
