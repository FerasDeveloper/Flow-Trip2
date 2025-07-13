<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
  use HasFactory;

  protected $fillable = [
    'name'
  ];

  public function Owner()
  {
    return $this->belongsToMany(Owner::class);
  }
}
