<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialController extends Controller
{
  public function redirectToGoogle()
  {
    return Socialite::driver('google')->redirect();
  }

  public function redirectToFacebook()
  {
    return Socialite::driver('facebook')->redirect();
  }

  // public function handleGoogleCallback()
  // {
  //   try {
  //     $user = Socialite::driver('google')->user();
  //     $find_user = User::query()->where('social_id', $user->id)->first();

  //     if ($find_user) {
  //       $find_user->tokens()->delete();
  //       $find_user['token'] = $find_user->createToken('AccessToken')->plainTextToken;
  //       return response()->json($find_user);
  //     } else {
  //       $new_user = User::query()->create([
  //         'name' => $user->name,
  //         'email' => $user->email,
  //         'phone_number' => $user->phone_number,
  //         'status' => 0,
  //         'role_id' => 3,
  //         'social_id' => $user->id,
  //         'social_type' => 'google',
  //       ]);

  //       $new_user['token'] = $new_user->createToken('AccessToken')->plainTextToken;
  //       return response()->json($new_user);
  //     }
  //   } catch (Exception $e) {
  //     return response()->json([
  //       'success' => false,
  //       'message' => 'Google authentication failed',
  //       'error' => $e->getMessage()
  //     ], 500);
  //   }
  // }


  public function handleGoogleCallback()
  {
    try {
      $user = Socialite::driver('google')->user();
      $find_user = User::query()->where('social_id', $user->id)->first();

      if ($find_user) {
        $find_user->tokens()->delete();
        $find_user['token'] = $find_user->createToken('AccessToken')->plainTextToken;

        // Redirect to frontend with user data as URL parameters
        $redirectUrl = 'http://localhost:3000/auth?' . http_build_query([
          'auth_success' => 'true',
          'token' => $find_user['token'],
          'name' => urlencode($find_user->name),
          'email' => urlencode($find_user->email),
          'id' => $find_user->id,
          'role_id' => $find_user->role_id
        ]);

        return redirect($redirectUrl);
      } else {
        $new_user = User::query()->create([
          'name' => $user->name,
          'email' => $user->email,
          'phone_number' => $user->phone_number,
          'status' => 0,
          'role_id' => 3,
          'social_id' => $user->id,
          'social_type' => 'google',
        ]);

        $new_user['token'] = $new_user->createToken('AccessToken')->plainTextToken;

        // Redirect to frontend with user data as URL parameters
        $redirectUrl = 'http://localhost:3000/auth?' . http_build_query([
          'auth_success' => 'true',
          'token' => $new_user['token'],
          'name' => urlencode($new_user->name),
          'email' => urlencode($new_user->email),
          'id' => $new_user->id,
          'role_id' => $new_user->role_id
        ]);

        return redirect($redirectUrl);
      }
    } catch (Exception $e) {
      // Redirect to frontend with error
      $redirectUrl = 'http://localhost:3000/auth?' . http_build_query([
        'auth_success' => 'false',
        'error' => urlencode($e->getMessage())
      ]);

      return redirect($redirectUrl);
    }
  }

  public function handleFacebookCallback()
  {
    try {
      $user = Socialite::driver('facebook')->user();
      $find_user = User::query()->where('social_id', $user->id)->first();

      if ($find_user) {
        Auth::login($find_user);
        return response()->json($find_user);
      } else {
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
    } catch (Exception $e) {
      dd($e->getMessage());
    }
  }
}
