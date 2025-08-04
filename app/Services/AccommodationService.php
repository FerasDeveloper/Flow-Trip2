<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Accommodation_type;
use App\Models\Owner;
use App\Models\Room;
use App\Models\Room_picture;
use App\Models\User;
use App\Models\User_accommodation;
use App\Models\User_room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccommodationService
{

  // Filter records depends on user name
  public function filter_name_accommodation($request)
  {
    $name = $request['name'];
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if ($accommodation == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

      $allUsers = collect();
      foreach ($rooms as $room) {
        $customersIds = User_room::query()->where('room_id', $room->id)->pluck('user_id')->toArray();
        $users = User::query()->whereIn('id', $customersIds)->get();
        $allUsers = $allUsers->merge($users);
      }

      if ($name) {
        $filteredUsers = $allUsers->filter(function ($user) use ($name) {
          return stripos($user->name, $name) !== false;
        })->values();
      } else {
        $filteredUsers = $allUsers->values();
      }

      $data['filtered_users'] = $filteredUsers;

      return response()->json($data);
    } else {
      $user_accommodations = User_accommodation::query()->where('accommodation_id', $accommodation->id)->pluck('user_id')->toArray();
      $users = User::query()->whereIn('id', $user_accommodations)->get();
      if ($name) {
        $filteredUsers = $users->filter(function ($user) use ($name) {
          return stripos($user->name, $name) !== false;
        })->values();
      } else {
        $filteredUsers = $users->values();
      }
      $data['filtered_users'] = $filteredUsers;
      return response()->json($data);
    }
  }

  public function show_offers()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

    if ($rooms->isEmpty()) {
      return response()->json([
        'message' => "There is no offers"
      ]);
    }

    $offers = [];
    foreach ($rooms as $room) {
      if ($room->offer_price >= 1) {
        $offers[] = $room;
      }
    }
    return response()->json([
      'offers' => $offers
    ]);
  }

  public function show_all_rooms()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();
    if ($rooms->isEmpty()) {
      return response()->json([
        'message' => "There is no rooms"
      ]);
    }
    return response()->json([
      'data' => $rooms
    ]);
  }

  public function add_room($request, $images)
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();

    if ($accommodation == null) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }

    $room = Room::query()->create([
      'accommodation_id' => $accommodation->id,
      'price' => $request['price'],
      'area' => $request['area'],
      'people_count' => $request['people_count'],
      'description' => $request['description'],
      'room_number' => $request['room_number']
    ]);

    if (!empty($images)) {
      foreach ($images as $image) {
        $imagePath = $image->store('images', 'public');
        Room_picture::query()->create([
          'room_id' => $room->id,
          'room_picture' => 'storage/' . $imagePath
        ]);
      }
    }
    return response()->json([
      'message' => 'Room added successfully'
    ]);
  }

  public function show_room($id)
  {
    $room = Room::query()->where('id', $id)->first();

    if ($room == null) {
      return response()->json([
        'message' => "Somthing went wrong"
      ]);
    }

    $pictures = Room_picture::query()->where('room_id', $id)->get();
    return response()->json([
      'room' => $room,
      'pictures' => $pictures
    ]);
  }

  public function edit_room($request, $remainingPictureIds, $deletedPictureIds, $images, $offer_price, $id)
  {
    $room = Room::query()->where('id', $id)->first();

    if (!$room) {
      return response()->json([
        'message' => 'Room not found'
      ], 404);
    }

    if ($offer_price != null && $offer_price != "") {
      $room->update([
        'price' => $request['price'],
        'area' => $request['area'],
        'people_count' => $request['people_count'],
        'description' => $request['description'],
        'offer_price' => $offer_price,
        'room_number' => $request['room_number']
      ]);
    } else {
      $room->update([
        'price' => $request['price'],
        'area' => $request['area'],
        'people_count' => $request['people_count'],
        'description' => $request['description'],
        'offer_price' => 0.00,
        'room_number' => $request['room_number']
      ]);
    }

    if (!empty($deletedPictureIds)) {
      $deletedImages = Room_picture::whereIn('id', $deletedPictureIds)->get();
      foreach ($deletedImages as $deletedImage) {
        $fileName = basename($deletedImage->room_picture);
        Storage::disk('public')->delete("images/{$fileName}");
        $deletedImage->delete();
      }
    }

    if (!empty($remainingPictureIds)) {
      $allOldImages = Room_picture::where('room_id', $id)->get();
      foreach ($allOldImages as $oldImage) {
        if (!in_array($oldImage->id, $remainingPictureIds)) {
          $fileName = basename($oldImage->room_picture);
          Storage::disk('public')->delete("images/{$fileName}");
          $oldImage->delete();
        }
      }
    }

    foreach ($images as $image) {
      $imagePath = $image->store('images', 'public');
      Room_picture::query()->create([
        'room_id' => $id,
        'room_picture' => 'storage/' . $imagePath
      ]);
    }


    return response()->json([
      'message' => 'Room updated successfully',
      'room' => $room->fresh(),
      'pictures' => Room_picture::where('room_id', $id)->get()
    ]);
  }

  public function delete_room($id)
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $room = Room::query()->where('id', $id)->first();

    if (!$room) {
      return response()->json([
        'message' => 'Room not found'
      ], 404);
    }

    if ($room->accommodation_id != $accommodation->id) {
      return response()->json([
        'message' => 'It is not your room'
      ], 403);
    }

    $pictures = Room_picture::query()->where('room_id', $room->id)->get();
    if (!$pictures->isEmpty()) {
      foreach ($pictures as $picture) {
        $imageUrl = $picture->room_picture;
        $fileName = basename(parse_url($imageUrl, PHP_URL_PATH));
        Storage::disk('public')->delete("images/{$fileName}");
        $picture->delete();
      }
    }

    $room->delete();

    return response()->json([
      'message' => 'Room deleted successfully'
    ]);
  }

  public function show_records()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->latest()->get();

      $roomsWithData = $rooms->map(function ($room) {
        $customersCount = User_room::query()->where('room_id', $room->id)->count();
        $room['count'] = $customersCount;
        return $room;
      });
      $data['rooms'] = $roomsWithData;

      return response()->json(
        $data
      );
    }

    $user_accommodations = User_accommodation::query()->where('accommodation_id', $accommodation->id)->latest()->get();

    $details = $user_accommodations->map(function ($user_accommodation) {
      $user_accommodation['user'] = User::query()->where('id', $user_accommodation->user_id)->first();
      return $user_accommodation;
    });
    $data['details'] = $details;

    return response()->json(
      $data
    );
  }

  public function show_room_records($id)
  {
    $room = Room::query()->where('id', $id)->first();
    if (!$room) {
      return response()->json([
        'message' => 'Room not found'
      ], 404);
    }
    $customersIds = User_room::query()->where('room_id', $room->id)->latest()->get();
    foreach ($customersIds as $customersId) {
      $user = User::query()->where('id', $customersId->user_id)->first();
      $customersId['user'] = $user;
    }

    return response()->json(
      $customersIds
    );
  }

  public function show_old_room_records($id)
  {
    $room = Room::query()->where('id', $id)->first();
    if (!$room) {
      return response()->json([
        'message' => 'Room not found'
      ], 404);
    }
    $customersIds = User_room::query()->where('room_id', $room->id)->orderBy('created_at', 'asc')->get();
    foreach ($customersIds as $customersId) {
      $user = User::query()->where('id', $customersId->user_id)->first();
      $customersId['user'] = $user;
    }

    return response()->json(
      $customersIds
    );
  }

  public function show_old_records()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if (!$accommodation) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

      $roomsWithLatestBooking = $rooms->map(function ($room) {
        $latestBooking = User_room::query()
          ->where('room_id', $room->id)
          ->orderBy('created_at', 'asc')
          ->first();
        $room['latest_booking'] = $latestBooking ? $latestBooking->created_at : null;
        $room['count'] = User_room::query()->where('room_id', $room->id)->count();
        return $room;
      });

      $sortedRooms = $roomsWithLatestBooking->sortBy('latest_booking')->values();
      $data['rooms'] = $sortedRooms;

      return response()->json(
        $data
      );
    }

    $user_accommodations = User_accommodation::query()->where('accommodation_id', $accommodation->id)->orderBy('created_at', 'asc')->get();

    $details = $user_accommodations->map(function ($user_accommodation) {
      $user_accommodation['user'] = User::query()->where('id', $user_accommodation->user_id)->first();
      return $user_accommodation;
    });
    $data['details'] = $details;

    return response()->json(
      $data
    );
  }

  public function show_popular_records()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if (!$accommodation) {
      return response()->json([
        'message' => 'Something went wrong'
      ]);
    }

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

      $roomsWithPopularBooking = $rooms->map(function ($room) {
        $room['count'] = User_room::query()->where('room_id', $room->id)->count();
        return $room;
      });

      $sortedRooms = $roomsWithPopularBooking->sortByDesc('count')->values();
      $data['rooms'] = $sortedRooms;

      return response()->json(
        $data
      );
    }

    $user_accommodations = User_accommodation::query()->where('accommodation_id', $accommodation->id)->orderBy('created_at', 'asc')->get();
    $monthsData = [];
    foreach ($user_accommodations as $user_accommodation) {
      $monthName = \Carbon\Carbon::parse($user_accommodation->created_at)->format('F');
      $user_accommodation['user'] = User::query()->where('id', $user_accommodation->user_id)->first();
      if (!isset($monthsData[$monthName])) {
        $monthsData[$monthName] = [
          'count' => 0,
          'items' => []
        ];
      }
      $monthsData[$monthName]['items'][] = $user_accommodation;
      $monthsData[$monthName]['count'] += 1;
    }
    $data['months'] = $monthsData;

    return response()->json($data);
  }
}
