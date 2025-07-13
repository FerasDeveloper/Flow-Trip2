<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package_element extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function packages(){
      return $this->belongsTo(Package::class);
    }
    public function package_element_picture(){
      return $this->hasMany(Package_element_picture::class);
    }
}
