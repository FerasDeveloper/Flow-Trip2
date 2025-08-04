<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Accommodation_type;
use App\Models\Activity;
use App\Models\Activity_owner;
use App\Models\Air_line;
use App\Models\Auth_request;
use App\Models\Owner;
use App\Models\Tourism_company;
use App\Models\User;
use App\Models\Vehicle_owner;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AuthRequestService
{
  protected function ensureAdmin(): void
  {
    $user = Auth::user();
    if (! in_array($user->role_id, [1, 2])) {
      throw new HttpResponseException(
        response()->json(
          ['message' => 'Authorization required']
        )
      );
    }
  }
  
  public function get_all_requests()
  {
    $this->ensureAdmin();
    $requests = Auth_request::query()->get();
    $data = [];

    if ($requests->isEmpty()) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }

    foreach ($requests as $request) {
      $user = User::query()->where('id', $request->user_id)->first();
      $data[] = [
        'request' => $request,
        'user_name' => $user->name
      ];
    }

    return response()->json([
      'data' => $data
    ]);
  }

  public function show_request($id)
  {
    $this->ensureAdmin();
    $data = [];
    $request = Auth_request::query()->where('id', $id)->first();
    if ($request == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }
    $user = User::query()->where('id', $request->user_id)->first();
    $data[] = [
      'request' => $request,
      'user_name' => $user->name,
      'email' => $user->email,
      'phone_number' => $user->phone_number,
    ];
    return response()->json([
      'data' => $data[0]
    ]);
  }

  public function edit_request($request, $id)
  {
    $this->ensureAdmin();
    if ($id == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }
    $update = Auth_request::query()->where('id', $id)->update([
      'activity_name' => $request['activity_name']
    ]);

    if ($update) {
      return response()->json([
        'message' => 'Request updated successfully'
      ]);
    }
  }

  public function accept_request($id)
  {
    $this->ensureAdmin();
    if ($id == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }
    $request = Auth_request::query()->where('id', $id)->first();
    if ($request == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }
    $owner = Owner::query()->create([
      'location' => $request->location,
      'country_id' => $request->country_id,
      'description' => $request->description,
      'user_id' => $request->user_id,
      'owner_category_id' => $request->owner_category_id,
    ]);

    $id = $request->owner_category_id;
    if ($id == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }
    switch ($id) {
      case '1':
        $accommodation_type = Accommodation_type::query()->where('name', $request->accommodation_type)->first();
        if (!$accommodation_type) {
          $accommodation_type = Accommodation_type::query()->create([
            'name' => $request->accommodation_type
          ]);
        }
        $owner['more'] = Accommodation::query()->create([
          'owner_id' => $owner->id,
          'accommodation_type_id' => $accommodation_type->id,
        ]);
        break;

      case '2':
        $owner['more'] = Air_line::query()->create([
          'owner_id' => $owner->id,
          'air_line_name' => $request->business_name
        ]);
        break;

      case '3':
        $owner['more'] = Tourism_company::query()->create([
          'owner_id' => $owner->id,
          'company_name' => $request->business_name
        ]);
        break;

      case '4':
        $owner['more'] = Vehicle_owner::query()->create([
          'owner_id' => $owner->id,
          'owner_name' => $request->business_name
        ]);
        break;

      case '5':
        $activity = Activity::query()->where('name', $request->activity_name)->first();
        if (!$activity) {
          $activity = Activity::query()->create([
            'name' => $request->activity_name
          ]);
        }
        $owner['more'] = Activity_owner::query()->create([
          'owner_id' => $owner->id,
          'owner_name' => $request->business_name,
          'activity_id' => $activity->id
        ]);
        break;

      default:
        break;
    }
    $user0 = User::query()->where('id', $request->user_id)->first();
    $user0->update([
      'status' => 0
    ]);

    $request->delete();
    return response()->json([
      'message' => 'Request accepted successfully',
      'data' => $owner
    ]);
  }

  public function delete_request($id)
  {
    $this->ensureAdmin();
    if ($id == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }
    $request = Auth_request::query()->where('id', $id)->first();
    if ($request == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }
    $user = User::query()->where('id', $request->user_id)->first();
    $delete = $request->delete();
    $deleteUser = $user->delete();
    if ($delete && $deleteUser) {
      return response()->json([
        'message' => 'Request deleted successfully'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong'
    ]);
  }
}
