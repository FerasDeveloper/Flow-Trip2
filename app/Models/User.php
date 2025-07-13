<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'status',
        'role_id',
        'social_id',
        'social_type'
    ];

    public function Role(){
      return $this->belongsToMany(Role::class);
    }

    public function Rate(){
      return $this->hasMany(Rate::class);
    }

    public function Account(){
      return $this->hasOne(Account::class);
    }

    public function Owner(){
      return $this->hasOne(Owner::class);
    }

    public function Notification(){
      return $this->hasMany(Notification::class);
    }

    public function Accommodation(){
      return $this->belongsToMany(Accommodation::class);
    }

    public function Room(){
      return $this->belongsToMany(Room::class);
    }

    public function Flight(){
      return $this->belongsToMany(Flight::class);
    }

    public function Package(){
      return $this->belongsToMany(Package::class);
    }

    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
