<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_package extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id',
    'package_id',
    'traveler_name',
    'national_number'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
  
}
