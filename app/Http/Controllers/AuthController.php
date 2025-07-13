<?php

namespace App\Http\Controllers;

use App\Models\Auth_request;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
  public function user_Register(Request $request): \Illuminate\Http\JsonResponse
  {
    $request->validate([
      'name' => 'required',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:8',
      'role_id' => 'required',
      'phone_number' => 'unique:users',
    ]);
    $code = rand(111111, 999999);

    if($request->phone_number != null){
    $user = User::query()->create([
      'name' => $request['name'],
      'email' => $request['email'],
      'password' => $request['password'],
      'phone_number' => $request['phone_number'],
      'role_id' => $request['role_id'],
    ]);
  }
  else{
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

    if($user['role_id'] == 3){
        $user['token'] = $user->createToken('AccessToken')->plainTextToken;
        return response()->json([
          'message' => 'User Created Successfully',
          'token' => $user['token']
        ]);
    }
    else if($user['role_id'] == 4){
        $user->update( [ 'status' => 1 ] );
    }

     return response()->json([
      'message' => 'User Created Successfully',
    ]);

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

    return response()->json([
      'message' => 'We have sent the code to your email address'
    ]);
  }

  public function verification(Request $request, string $email)
  {
    $user = User::query()->where('email', $email)->first();
    $cache_value = Cache::get($user->id);
    $request->validate([
      'verification_code' => 'required|min:6|max:6',
    ]);
    if($cache_value && ($request['verification_code'] == $cache_value)){
      $update = User::query()->where('email', $email)->first();
      $user->email_verified_at = now();
      $user->save();

      return response()->json([
        'message' => 'your email has been verified successfully'
      ]);
    }
    return response()->json([
      'message' => 'the code is wrong, please try again'
    ]);
  }
    

  public function reset_password(Request $request, string $email)
  {
    $user = User::query()->where('email', $email)->first();
    $request->validate([
      'new_password' => 'required|min:8',
    ]);
    
    $user->password = $request['new_password'];
    $user->save();

    return response()->json([
      'message' => 'your password has changed successfully'
    ]);

  }


  public function create_owner(Request $request, string $email)
  {
    $user = User::query()->where('email', $email)->first();
    if ($user['role_id'] != 4) {
      return response()->json([
        'message' => 'something went wrong'
      ]);
    }

    $id = $user['id'];
    $owner = Owner::query()->where('user_id', $id)->first();
    $auth_request = Auth_request::query()->where('user_id', $id)->first();
    if ($owner || $auth_request) {
      return response()->json([
        'message' => 'You already have an account.'
      ]);
    }

    $request->validate([
      'owner_category_id' => 'required',
      'country_id' => 'required',
      'location' => 'required',
      'description' => 'required',
      'business_name' => 'required',
    ]);

    $create_auth_request = Auth_request::query()->create([
      'owner_category_id' => $request['owner_category_id'],
      'country_id' => $request['country_id'],
      'location' => $request['location'],
      'description' => $request['description'],
      'business_name' => $request['business_name'],
      'user_id' => $user['id'],
    ]);
    if($request['owner_category_id'] == 1){
        Auth_request::query()->where('user_id', $id)->update( [ 
            'accommodation_type' => $request['accommodation_type'] 
        ] );
    }
    else if($request['owner_category_id'] == 5){
        Auth_request::query()->where('user_id', $id)->update( [ 
            'activity_name' => $request['activity_name'] 
        ] );
    }

    return response()->json([
      'message' => 'your request has been sent to the technical team.. pleas wait until the request processed.',
    ]);
  }


  public function login(Request $request)
  {

    $request->validate([
      'email' => 'required',
      'password' => 'required'
    ]);

    $user = User::query()->where('email', $request['email'])->first();

    if ($user && Hash::check($request['password'], $user->password)) {
        if ($user['status'] == 2) {
          return response()->json([
            'message' => 'You are panned from using this web application.'
          ]);
        }
        else if ($user['status'] == 1) {
          return response()->json([
            'message' => 'Your request is still being processed.'
          ]);
        }

        $user['token'] = $user->createToken('AccessToken')->plainTextToken;
        return response()->json([
          'message' => 'Welcome',
          'token' => $user['token'],
        ]);
    }
    
    return response()->json([
      'message' => 'your Email does not match with Password..Please try again'
    ]);
  }

  public function logout()
  {
    Auth::user()->currentAccessToken()->delete();
    return response()->json([
      'message' => 'You logged out successfully'
    ]);
  }
}
