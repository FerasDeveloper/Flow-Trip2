<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner_service extends Model
{
    use HasFactory;
    protected $table = 'owner_services';
    protected $fillable = [
      'service_id',
      'owner_id'
    ];

    public function Service(){
      return $this->belongsToMany(Service::class);
    }

    public function Owner(){
      return $this->belongsToMany(Owner::class);
    }

}
