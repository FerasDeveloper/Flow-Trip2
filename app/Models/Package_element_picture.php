<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package_element_picture extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function package_element(){
      return $this->belongsTo(Package_element::class);
    }
}
