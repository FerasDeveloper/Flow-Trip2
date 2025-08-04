<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirectToGoogle(){
      return Socialite::driver('google')->redirect();
    }

    public function redirectToFacebook(){
      return Socialite::driver('facebook')->redirect();
    }

    public function handleGoogleCallback(){
      try{
        $user = Socialite::driver('google')->user();
        $find_user = User::query()->where('social_id', $user->id)->first();

        if($find_user){
          Auth::login($find_user);
          return response()->json($find_user);
        }
        else{
          $new_user = User::query()->create([
            'name' => $user->name,
            'email' => $user->email,
            // 'password' => Hash::make('my-google'),
            'phone_number' => $user->phone_number,
            'status' => 0,
            'role_id' => 3,
            'social_id' => $user->id,
            'social_type' => 'google',
          ]);

          Auth::login($new_user);
          return response()->json($new_user);

        }
      }
      catch(Exception $e){
        dd($e->getMessage());
      }
    }

    public function handleFacebookCallback(){
      try{
        $user = Socialite::driver('facebook')->user();
        $find_user = User::query()->where('social_id', $user->id)->first();

        if($find_user){
          Auth::login($find_user);
          return response()->json($find_user);
        }
        else{
          $new_user = User::query()->create([
            'name' => $user->name,
            'email' => $user->email,
            'password' => Hash::make('my-facebook'),
            'phone_number' => $user->phone_number,
            'status' => 0,
            'role_id' => 3,
            'social_id' => $user->id,
            'social_type' => 'facebook',
          ]);

          Auth::login($new_user);
          return response()->json($new_user);

        }
      }
      catch(Exception $e){
        dd($e->getMessage());
      }
    }

}
