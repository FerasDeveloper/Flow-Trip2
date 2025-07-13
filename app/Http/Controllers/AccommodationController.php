<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Accommodation_type;
use App\Models\Owner;
use App\Models\Room;
use App\Models\Room_picture;
use App\Models\User;
use App\Models\User_accommodation;
use App\Models\User_room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isEmpty;

class AccommodationController extends Controller
{

  public function show_records()
  {

    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

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

    $user_accommodations = User_accommodation::query()->where('accommodation_id', $accommodation->id)->get();

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

    $customersIds = User_room::query()->where('room_id', $room->id)->get();
    foreach($customersIds as $customersId){
      $user = User::query()->where('id', $customersId->user_id)->first();
      $customersId['user'] = $user;
    }

    return response()->json(
      $customersIds
    );
  }

  public function filter_name_accommodation(Request $request)
  {
    $name = $request->input('name');
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

      // جمع كل المستخدمين في Collection واحدة
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
    }

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

  public function show_offers()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

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

  public function show_all_rooms(){

    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

    return response()->json([
      'data' => $rooms
    ]);

  }


  public function add_room(Request $request)
  {

    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();

    $request->validate([
      'price' => 'required|numeric|min:1',
      'area' => 'required|numeric|min:1',
      'people_count' => 'required|integer|min:1',
      'description' => 'required',
      'room_number' => 'required'
    ], [
      'price.min' => 'The price must be a positive value greater than zero.',
      'area.min' => 'The area must be a positive value greater than zero.',
      'people_count.min' => 'The number of people must be a positive value greater than zero.',
    ]);

    $room = Room::query()->create([
      'accommodation_id' => $accommodation->id,
      'price' => $request->price,
      'area' => $request->area,
      'people_count' => $request->people_count,
      'description' => $request->description,
      'room_number' => $request->room_number
    ]);

    if ($request->hasFile('images')) {
      foreach ($request->file('images') as $image) {
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
    $pictures = Room_picture::query()->where('room_id', $id)->get();
    return response()->json([
      'room' => $room,
      'pictures' => $pictures
    ]);
  }

  // public function edit_room(Request $request, $id)
  // {

  //   $request->validate([
  //     'price' => 'required|numeric|min:1',
  //     'area' => 'required|numeric|min:1',
  //     'people_count' => 'required|integer|min:1',
  //     'description' => 'required',
  //   ], [
  //     'price.min' => 'The price must be a positive value greater than zero.',
  //     'area.min' => 'The area must be a positive value greater than zero.',
  //     'people_count.min' => 'The number of people must be a positive value greater than zero.',
  //   ]);

  //   $room = Room::query()->where('id', $id)->first();

  //   if ($request->offer_price != null) {
  //     $room->update([
  //       'price' => $request->price,
  //       'area' => $request->area,
  //       'people_count' => $request->people_count,
  //       'description' => $request->description,
  //       'offer_price' => $request->offer_price
  //     ]);
  //   } else {
  //     $room->update([
  //       'price' => $request->price,
  //       'area' => $request->area,
  //       'people_count' => $request->people_count,
  //       'description' => $request->description,
  //       'offer_price' => 0.00
  //     ]);
  //   }

  //   if ($request->hasFile('images')) {
  //     $oldImages = Room_picture::where('room_id', $id)->get();
  //     foreach ($oldImages as $oldImage) {
  //       $ted = $oldImage->room_picture;
  //       $fileName = basename($ted);
  //       Storage::disk('public')->delete("images/{$fileName}");
  //       $oldImage->delete();
  //     }

  //     foreach ($request->file('images') as $image) {
  //       $imagePath = $image->store('images', 'public');
  //       Room_picture::query()->create([
  //         'room_id' => $id,
  //         'room_picture' => 'storage/' . $imagePath
  //       ]);
  //     }
  //   }
  //   return response()->json([
  //     'message' => 'Room updated successfully'
  //   ]);
  // }

  public function edit_room(Request $request, $id)
{
    $request->validate([
        'price' => 'required|numeric|min:1',
        'area' => 'required|numeric|min:1',
        'people_count' => 'required|integer|min:1',
        'description' => 'required',
    ], [
        'price.min' => 'The price must be a positive value greater than zero.',
        'area.min' => 'The area must be a positive value greater than zero.',
        'people_count.min' => 'The number of people must be a positive value greater than zero.',
    ]);

    $room = Room::query()->where('id', $id)->first();

    if (!$room) {
        return response()->json([
            'message' => 'Room not found'
        ], 404);
    }

    if ($request->offer_price != null && $request->offer_price != "") {
        $room->update([
            'price' => $request->price,
            'area' => $request->area,
            'people_count' => $request->people_count,
            'description' => $request->description,
            'offer_price' => $request->offer_price
        ]);
    } else {
        $room->update([
            'price' => $request->price,
            'area' => $request->area,
            'people_count' => $request->people_count,
            'description' => $request->description,
            'offer_price' => 0.00
        ]);
    }

    $remainingPictureIds = [];
    $deletedPictureIds = [];

    if ($request->has('remaining_picture_ids')) {
        $remainingPictureIds = json_decode($request->remaining_picture_ids, true);
    }

    if ($request->has('deleted_picture_ids')) {
        $deletedPictureIds = json_decode($request->deleted_picture_ids, true);
    }

    // حذف الصور المحذوفة
    if (!empty($deletedPictureIds)) {
        $deletedImages = Room_picture::whereIn('id', $deletedPictureIds)->get();
        foreach ($deletedImages as $deletedImage) {
            $fileName = basename($deletedImage->room_picture);
            Storage::disk('public')->delete("images/{$fileName}");
            $deletedImage->delete();
        }
    }

    // حذف الصور التي لم يتم إدراجها في remaining_picture_ids
    $allOldImages = Room_picture::where('room_id', $id)->get();
    foreach ($allOldImages as $oldImage) {
        if (!in_array($oldImage->id, $remainingPictureIds)) {
            $fileName = basename($oldImage->room_picture);
            Storage::disk('public')->delete("images/{$fileName}");
            $oldImage->delete();
        }
    }

    // إضافة الصور الجديدة
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $imagePath = $image->store('images', 'public');
            Room_picture::query()->create([
                'room_id' => $id,
                'room_picture' => 'storage/' . $imagePath
            ]);
        }
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

    // حذف الصور من التخزين وقاعدة البيانات
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
}
