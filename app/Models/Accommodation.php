<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    use HasFactory;
    protected $fillable = [
      'owner_id',
      'accommodation_type_id',
    ];

    public function Owner(){
      return $this->belongsTo(Owner::class);
    }

    public function Accommodation_type(){
      return $this->belongsToMany(Accommodation_type::class);
    }

    public function Room(){
      return $this->hasMany(Room::class);
    }

    public function User()
    {
      return $this->belongsToMany(User::class);
    }
}
