<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation_type extends Model
{
    use HasFactory;
    protected $fillable = [
      'name'
    ];

    public function Accommodation(){
      return $this->hasMany(Accommodation::class);
    }
}
