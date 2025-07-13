<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Air_line extends Model
{
    use HasFactory;
    protected $fillable = [
      'owner_id',
      'air_line_name'
    ];

    public function Owner(){
      return $this->belongsTo(Owner::class);
    }

    public function Plane(){
      return $this->hasMany(Plane::class);
    }

    public function Flight(){
      return $this->hasMany(Flight::class);
    }
}
