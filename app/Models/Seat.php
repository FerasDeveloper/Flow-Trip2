<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function flight(){
      return $this->belongsToMany(Flight::class);
    }
}
