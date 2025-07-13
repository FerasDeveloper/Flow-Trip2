<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan_type extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function plane(){
      return $this->hasMany(Plane::class);
    }
}
