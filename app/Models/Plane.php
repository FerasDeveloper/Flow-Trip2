<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plane extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function airline(){
      return $this->belongsTo(Air_line::class);
    }
    public function plane_type(){
      return $this->belongsTo(Plan_type::class);
    }

    public function flights(){
      return $this->hasMany(Flight::class);
    }
}
