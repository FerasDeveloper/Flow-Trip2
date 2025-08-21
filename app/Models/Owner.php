<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
  use HasFactory;

  protected $fillable = [
    'description',
    'location',
    'user_id',
    'owner_category_id',
    'country_id',
  ];

  public function User()
  {
    return $this->belongsTo(User::class);
  }

  public function Owner_category(){
    return $this->belongsToMany(Owner_category::class);
  }

  public function Country(){
    return $this->belongsTo(Country::class);
  }

  public function Service(){
    return $this->belongsToMany(Service::class);
  }

  public function Picture(){
    return $this->hasMany(Picture::class);
  }

  public function Rate(){
    return $this->hasMany(Rate::class);
  }

  public function Accommodation(){
    return $this->hasOne(Accommodation::class);
  }

  public function Air_line(){
    return $this->hasOne(Air_line::class);
  }

  public function Tourism_company(){
    return $this->hasOne(Tourism_company::class);
  }

  public function Vehicle_owner(){
    return $this->hasOne(Vehicle_owner::class);
  }

  public function Activity_owner(){
    return $this->hasOne(Activity_owner::class);
  }
}
