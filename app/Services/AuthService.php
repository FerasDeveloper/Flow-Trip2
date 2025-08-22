<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Accommodation_type;
use App\Models\Auth_request;
use App\Models\Owner;
use App\Models\Owner_category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{

  public function user_Register(array $request)
  {

    $code = rand(111111, 999999);

    if ($request['phone_number'] != null) {
      $user = User::query()->create([
        'name' => $request['name'],
        'email' => $request['email'],
        'password' => $request['password'],
        'phone_number' => $request['phone_number'],
        'role_id' => $request['role_id'],
      ]);
    } else {
      $user = User::query()->create([
        'name' => $request['name'],
        'email' => $request['email'],
        'password' => $request['password'],
        'role_id' => $request['role_id'],
      ]);
    }
    $emailBody = "Hello {$user->name}!
    \n\nWelcome to FlowTrip! There is just one more step befor you reach the site.
    \nverify your email address by this verification code:
    \n\n                          {$code} 
    \n\nThank you for registering at our site.\n\nBest regards.";
    Cache::put($user->id, $code, now()->addMinutes(3));

    Mail::raw($emailBody, function ($message) use ($user) {
      $message->to($user->email)
        ->subject('Flow Trip - email verification');
    });

    if ($user['role_id'] == 3) {
      $user['token'] = $user->createToken('AccessToken')->plainTextToken;
      return ['token' => $user['token']];
    } else if ($user['role_id'] == 4) {
      $user->update(['status' => 1]);
    }

    return [];
  }


  public function resend_email(string $email)
  {
    $user = User::query()->where('email', $email)->first();
    $code = rand(111111, 999999);

    $emailBody = "Hello {$user->name}!
    \n\nWelcome to FlowTrip! There is just one more step befor you reach the site.
    \nverify your email address by this verification code:
    \n\n                          {$code} 
    \n\nThank you for registering at our site.\n\nBest regards.";
    Cache::put($user->id, $code, now()->addMinutes(3));

    Mail::raw($emailBody, function ($message) use ($user) {
      $message->to($user->email)
        ->subject('Flow Trip - email verification');
    });
  }

  public function verification(array $request, string $email)
  {
    $user = User::query()->where('email', $email)->first();
    $cache_value = Cache::get($user->id);

    if ($cache_value && ($request['verification_code'] == $cache_value)) {

      $update = User::query()->where('email', $email)->first();
      $user->email_verified_at = now();
      $user->save();
      return true;
    }
    return false;
  }


  public function reset_password(array $request, string $email)
  {
    $user = User::query()->where('email', $email)->first();

    $user->password = $request['new_password'];
    $user->save();
  }


  public function create_owner(array $request, string $email)
  {
    $user = User::query()->where('email', $email)->first();
    if ($user['role_id'] != 4) {
      return 'something went wrong';
    }

    $id = $user['id'];
    $owner = Owner::query()->where('user_id', $id)->first();
    $auth_request = Auth_request::query()->where('user_id', $id)->first();
    if ($owner || $auth_request) {
      return 'You already have an account.';
    }

    $create_auth_request = Auth_request::query()->create([
      'owner_category_id' => $request['owner_category_id'],
      'country_id' => $request['country_id'],
      'location' => $request['location'],
      'description' => $request['description'],
      'business_name' => $request['business_name'],
      'user_id' => $user['id'],
    ]);
    if ($request['owner_category_id'] == 1) {
      Auth_request::query()->where('user_id', $id)->update([
        'accommodation_type' => $request['accommodation_type']
      ]);
    } else if ($request['owner_category_id'] == 5) {
      Auth_request::query()->where('user_id', $id)->update([
        'activity_name' => $request['activity_name']
      ]);
    }

    return 'your request has been sent to the technical team.. pleas wait until the request processed.';
  }


  public function login(array $request)
  {

    $user = User::query()->where('email', $request['email'])->first();

    if ($user && Hash::check($request['password'], $user->password)) {
      if ($user['status'] == 2) {
        return 'banned';
      } else if ($user['status'] == 1) {
        return 'pending';
      }
      // else if ($user['email_verified_at'] == null) {
      //   return 'unverified';
      // }

      $user['token'] = $user->createToken('AccessToken')->plainTextToken;

      $roleInfo = $this->getRole($user);
      $responseData = [
        'message' => 'Welcome',
        'token' => $user['token'],
        'name' => $user['name'],
        'id' => $user['id'],
        'role' => $roleInfo['role']
      ];

      return $responseData;
    }
    return false;
  }

  public function logout()
  {
    Auth::user()->currentAccessToken()->delete();
  }


  public function getRole(User $user)
  {
    $role = Role::where('id', $user->role_id)->first();

    $result = [
      'role' => $role->role_name ?? 'Unknown'
    ];

    if ($role && $role->role_name === 'owner') {
      $owner = Owner::where('user_id', $user->id)->first();
      if (!$owner)
        return $result;

      $category = Owner_category::where('id', $owner->owner_category_id)->first();
      $categoryName = $category->name ?? 'Unknown category';

      if ($categoryName === 'Accommodation') {
        $accommodation = Accommodation::where('owner_id', $owner->id)->first();

        if ($accommodation) {
          $type = Accommodation_type::find($accommodation->accommodation_type_id);
          $result['role'] = $type->name ?? 'Accommodation type not found.';
        } else {
          $result['role'] = 'Accommodation record not found.';
        }
      } else {
        $result['role'] = $categoryName;
      }
    }

    return $result;
  }
}
