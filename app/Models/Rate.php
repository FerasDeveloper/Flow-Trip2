<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $table = 'rates';
    protected $fillable = [
      'rating',
      'user_id',
      'owner_id'
    ];

    public function User(){
      return $this->belongsToMany(User::class);
    }

    public function Owner(){
      return $this->belongsToMany(Owner::class);
    }
}
