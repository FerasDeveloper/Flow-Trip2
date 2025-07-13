<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
      'balance',
      'account_number',
      'user_id'
    ];

    public function User(){
      return $this->belongsTo(User::class);
    }
}
