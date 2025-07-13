<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner_category extends Model
{
    use HasFactory;

    protected $table = 'owner_categories';
    protected $fillable = [
      'name'
    ];

    public function Owner(){
      return $this->hasMany(Owner::class);
    }
}
