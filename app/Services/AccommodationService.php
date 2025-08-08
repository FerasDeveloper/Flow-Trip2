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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isEmpty;

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
      return ['message' => 'Something went wrong'];
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

      return $data;
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
      return $data;
    }
  }

  public function show_offers()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

    if ($rooms->isEmpty()) {
      return ['message' => "There is no offers"];
    }

    $offers = [];
    foreach ($rooms as $room) {
      if ($room->offer_price >= 1) {
        $offers[] = $room;
      }
    }
    return ['offers' => $offers];
  }

  public function show_all_rooms()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();
    if ($rooms->isEmpty()) {
      return ['message' => "There is no rooms"];
    }
    return ['data' => $rooms];
  }

  public function add_room($request, $images)
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();

    if ($accommodation == null) {
      return ['message' => 'Something went wrong'];
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
    return ['message' => 'Room added successfully'];
  }

  public function show_room($id)
  {
    $room = Room::query()->where('id', $id)->first();

    if ($room == null) {
      return ['message' => "Somthing went wrong"];
    }

    $pictures = Room_picture::query()->where('room_id', $id)->get();
    return [
      'room' => $room,
      'pictures' => $pictures
    ];
  }

  public function edit_room($request, $remainingPictureIds, $deletedPictureIds, $images, $offer_price, $id)
  {
    $room = Room::query()->where('id', $id)->first();

    if (!$room) {
      return ['message' => 'Room not found'];
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

    $allOldImages = Room_picture::where('room_id', $id)->get();
    if (!empty($remainingPictureIds)) {
      if (!$allOldImages->isEmpty()) {
        foreach ($allOldImages as $oldImage) {
          if (!in_array($oldImage->id, $remainingPictureIds)) {
            $fileName = basename($oldImage->room_picture);
            Storage::disk('public')->delete("images/{$fileName}");
            $oldImage->delete();
          }
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

    return [
      'message' => 'Room updated successfully',
      'room' => $room->fresh(),
      'pictures' => Room_picture::where('room_id', $id)->get()
    ];
  }

  public function delete_room($id)
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $room = Room::query()->where('id', $id)->first();

    if (!$room) {
      return [
        'message' => 'Room not found'
      ];
    }

    if ($room->accommodation_id != $accommodation->id) {
      return [
        'message' => 'It is not your room'
      ];
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

    return [
      'message' => 'Room deleted successfully'
    ];
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

      return $data;
    }

    $user_accommodations = User_accommodation::query()->where('accommodation_id', $accommodation->id)->latest()->get();

    $details = $user_accommodations->map(function ($user_accommodation) {
      $user_accommodation['user'] = User::query()->where('id', $user_accommodation->user_id)->first();
      return $user_accommodation;
    });
    $data['details'] = $details;

    return $data;
  }

  public function show_room_records($id)
  {
    $room = Room::query()->where('id', $id)->first();
    if (!$room) {
      return [
        'message' => 'Room not found'
      ];
    }
    $customersIds = User_room::query()->where('room_id', $room->id)->latest()->get();
    foreach ($customersIds as $customersId) {
      $user = User::query()->where('id', $customersId->user_id)->first();
      $customersId['user'] = $user;
    }

    return $customersIds;
  }

  public function show_old_room_records($id)
  {
    $room = Room::query()->where('id', $id)->first();
    if (!$room) {
      return [
        'message' => 'Room not found'
      ];
    }
    $customersIds = User_room::query()->where('room_id', $room->id)->orderBy('created_at', 'asc')->get();
    foreach ($customersIds as $customersId) {
      $user = User::query()->where('id', $customersId->user_id)->first();
      $customersId['user'] = $user;
    }

    return $customersIds;
  }

  public function show_old_records()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if (!$accommodation) {
      return [
        'message' => 'Something went wrong'
      ];
    }

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

      $roomsWithLatestBooking = $rooms->map(function ($room) {
        $latestBooking = User_room::query()
          ->where('room_id', $room->id)
          ->orderBy('start_date', 'asc')
          ->first();

        $room['latest_booking'] = $latestBooking ? $latestBooking->start_date : null;
        $room['count'] = User_room::query()
          ->where('room_id', $room->id)
          ->count();

        return $room;
      });

      $sortedRooms = $roomsWithLatestBooking->sortBy('latest_booking')->values();
      $data['rooms'] = $sortedRooms;

      return $data;
    }

    $user_accommodations = User_accommodation::query()->where('accommodation_id', $accommodation->id)->orderBy('created_at', 'asc')->get();

    $monthsData = [];

    foreach ($user_accommodations as $item) {
      // مفتاح الفرز: "سنة-شهر"
      $monthKey  = Carbon::parse($item->start_date)->format('Y-m');
      // الاسم الظاهر: مثل "January 2025"
      $monthName = Carbon::parse($item->start_date)->format('F Y');

      if (!isset($monthsData[$monthKey])) {
        $monthsData[$monthKey] = [
          'month' => $monthName,
          'count' => 0,
          'items' => []
        ];
      }

      $monthsData[$monthKey]['items'][] = $item;
      $monthsData[$monthKey]['count']++;
    }

    // نرتب مفاتيح المصفوفة تصاعدياً
    ksort($monthsData);

    // نُعيد الفهرسة الرقمية حتى تتناسب مع JSON
    $data['months'] = array_values($monthsData);

    return $data;
  }

  public function show_new_records()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if (!$accommodation) {
      return [
        'message' => 'Something went wrong'
      ];
    }

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()
        ->where('accommodation_id', $accommodation->id)
        ->get();

      $roomsWithNewestBooking = $rooms->map(function ($room) {
        $newestBooking = User_room::query()
          ->where('room_id', $room->id)
          ->orderBy('start_date', 'desc')
          ->first();

        $room['newest_booking'] = $newestBooking ? $newestBooking->start_date : null;
        $room['count'] = User_room::query()
          ->where('room_id', $room->id)
          ->count();

        return $room;
      });

      $sortedRooms = $roomsWithNewestBooking
        ->sortByDesc('newest_booking')
        ->values();

      $data['rooms'] = $sortedRooms;

      return $data;
    }


    $user_accommodations = User_accommodation::query()
      ->where('accommodation_id', $accommodation->id)
      ->orderBy('created_at', 'desc')
      ->get();

    $monthsData = [];

    foreach ($user_accommodations as $item) {
      $monthKey  = Carbon::parse($item->start_date)->format('Y-m');
      $monthName = Carbon::parse($item->start_date)->format('F Y');

      if (! isset($monthsData[$monthKey])) {
        $monthsData[$monthKey] = [
          'month' => $monthName,
          'count' => 0,
          'items' => []
        ];
      }

      $monthsData[$monthKey]['items'][] = $item;
      $monthsData[$monthKey]['count']++;
    }

    // 2. فرز الأشهر تنازلياً
    krsort($monthsData);

    // 3. إعادة الفهرسة للتحويل إلى JSON
    $data['months'] = array_values($monthsData);

    return $data;
  }

  public function show_popular_records()
  {
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
    $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->select('name')->first();
    $data = [];

    if (!$accommodation) {
      return [
        'message' => 'Something went wrong'
      ];
    }

    if ($accommodation_type->name == "Hotel") {
      $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

      $roomsWithPopularBooking = $rooms->map(function ($room) {
        $room['count'] = User_room::query()->where('room_id', $room->id)->count();
        return $room;
      });

      $sortedRooms = $roomsWithPopularBooking->sortByDesc('count')->values();
      $data['rooms'] = $sortedRooms;

      return $data;
    }


    $user_accommodations = User_accommodation::query()
      ->where('accommodation_id', $accommodation->id)
      ->orderBy('created_at', 'asc')
      ->get();


    $monthsData = [];

    foreach ($user_accommodations as $item) {
      $key       = Carbon::parse($item->start_date)->format('Y-m');
      $monthName = Carbon::parse($item->start_date)->format('F Y');

      if (! isset($monthsData[$key])) {
        $monthsData[$key] = [
          'month' => $monthName,
          'count' => 0,
          'items' => []
        ];
      }

      $monthsData[$key]['items'][] = $item;
      $monthsData[$key]['count']++;
    }

    $data['months'] = collect($monthsData)
      ->values()                      // نتخلص من المفاتيح الزمنية
      ->sortByDesc('count')           // الفرز تنازليًا حسب عدد الحجوزات
      ->values()                      // إعادة فهرسة الأرقام
      ->all();

    return $data;
  }
}
