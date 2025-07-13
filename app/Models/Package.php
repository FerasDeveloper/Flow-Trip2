<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tourism_company(){
      return $this->belongsTo(Tourism_company::class);
    }

    public function package_element(){
      return $this->hasMany(Package_element::class);
    }

    public function User(){
      return $this->belongsToMany(User::class);
    }
}
