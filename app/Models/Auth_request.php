<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auth_request extends Model
{
    use HasFactory;
    protected $fillable = [
    'description',
    'location',
    'user_id',
    'owner_category_id',
    'country_id',
    'business_name'
  ];

  public function User()
  {
    return $this->belongsTo(User::class);
  }
}
