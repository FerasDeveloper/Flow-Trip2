<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Accommodation_type;
use App\Models\Activity;
use App\Models\Activity_owner;
use App\Models\Air_line;
use App\Models\Car_picture;
use App\Models\Car_type;
use App\Models\Country;
use App\Models\Owner;
use App\Models\Owner_category;
use App\Models\Owner_service;
use App\Models\Package;
use App\Models\Package_element;
use App\Models\Picture;
use App\Models\Room;
use App\Models\Room_picture;
use App\Models\Service;
use App\Models\Tourism_company;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vehicle_owner;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AdminService
{
  protected function ensureAdmin(): void
  {
    $user = Auth::user();
    if (! in_array($user->role_id, [1, 2])) {
      // رمي استثناء يجعل Laravel يردّ JSON بدل صفحة خطأ
      throw new HttpResponseException(
        response()->json(
          ['message' => 'Authorization required']
        )
      );
    }
  }

  public function get_all_owners()
  {
    $this->ensureAdmin();
    $owners = Owner::query()->get();
    $data = [];

    if ($owners->isEmpty()) {
      return ['message' => 'Something went wrong'];
    }

    foreach ($owners as $owner) {
      $category = Owner_category::query()->where('id', $owner->owner_category_id)->first();
      $country = Country::query()->where('id', $owner->country_id)->first();
      $user = User::query()->where('id', $owner->user_id)->first();

      $data[] = [
        'owner' => $owner,
        'category' => $category->name,
        'country' => $country->name,
        'user' => $user
      ];
    }
    return ['data' => $data];
  }

  public function show_owner($id)
  {
    $this->ensureAdmin();
    $owner = Owner::query()->where('id', $id)->first();
    if ($owner == null) {
      return ['message' => 'Something went wrong'];
    }
    $data = [];
    $data['owner'] = $owner;
    $owner['user'] = User::query()->where('id', $owner->user_id)->first();
    $country = Country::query()->where('id', $owner->country_id)->select('name')->first();
    $category = Owner_category::query()->where('id', $owner->owner_category_id)->select('name')->first();

    if ($category == null || $country == null) {
      return ['message' => 'Something went wrong'];
    }

    $owner['country'] = $country->name;
    $owner['category'] = $category->name;
    $category_id = $owner->owner_category_id;
    $data['pictures'] = Picture::query()->where('owner_id', $id)->get();
    $ffs = Owner_service::query()->where('owner_id', $id)->get();
    $data['services'] = [];

    foreach ($ffs as $ff) {
      $service = Service::query()->where('id', $ff->service_id)->first();
      $data['services'][] = $service;
    }

    if ($category_id == 1) {
      $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
      $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->first();

      $data['details'] = [
        'accommodation' => $accommodation,
        'accommodation_type' => $accommodation_type->name
      ];

      if ($accommodation_type->name == 'Hotel') {
        $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();


        $roomsWithPictures = $rooms->map(function ($room) {
          $room['pictures'] = Room_picture::query()->where('room_id', $room->id)->get();
          return $room;
        });

        $data['rooms'] = $roomsWithPictures;
      }
    } else if ($category_id == 2) {
      $air_line = Air_line::query()->where('owner_id', $owner->id)->first();
      $data['details'] = $air_line;
    } else if ($category_id == 3) {
      $tourism = Tourism_company::query()->where('owner_id', $owner->id)->first();
      $data['details'] = $tourism;

      $packages = Package::query()->where('tourism_company_id', $tourism->id)->get();

      $packagesWithElements  = $packages->map(function ($package) {
        $package['element'] = Package_element::query()->where('package_id', $package->id)->get();
        return $package;
      });
      $data['packages'] = $packagesWithElements;
    } else if ($category_id == 4) {
      $vehicle_owner = Vehicle_owner::query()->where('owner_id', $owner->id)->first();
      $data['details'] = $vehicle_owner;

      $vehicles = Vehicle::query()->where('vehicle_owner_id', $vehicle_owner->id)->get();

      $vehiclesWithPictures = $vehicles->map(function ($vehicle) {
        $vehicle['pictures'] = Car_picture::query()->where('vehicle_id', $vehicle->id)->get();
        $f = Car_type::query()->where('id', $vehicle->car_type_id)->first();
        $vehicle['car_type'] = $f->name;
        return $vehicle;
      });

      $data['vehicles'] = $vehiclesWithPictures;
    } else if ($category_id == 5) {
      $activity_owner = Activity_owner::query()->where('owner_id', $owner->id)->first();
      $activity = Activity::query()->where('id', $activity_owner->activity_id)->select('name')->first();
      $data['details'] = [
        'activity_owner' => $activity_owner,
        'activity' => $activity->name
      ];
    }
    return $data;
  }

  public function toggleBlockStatus($id)
  {
    $this->ensureAdmin();
    $user = User::find($id);

    if (!$user) {
      return ['message' => 'User not found'];
    }

    if ($user->status == 2) {
      $user->update(['status' => 0]);
      return ['message' => 'User unblocked successfully'];
    } else {
      $user->update(['status' => 2]);
      $user->tokens()->delete();
      return ['message' => 'User blocked successfully'];
    }
  }

  public function admin_search($request)
  {
    $this->ensureAdmin();
    $countrySearch = $request['country'];
    $nameSearch = $request['name'];
    $categorySearch = $request['category_id'];
    $data = [];

    if (empty($countrySearch) && empty($nameSearch) && empty($categorySearch)) {
      return ['message' => 'No Result'];
    }

    $query = Owner::query();

    if (!empty($countrySearch)) {
      $countryIds = Country::where('name', 'LIKE', "%{$countrySearch}%")
        ->pluck('id')
        ->toArray();
      $query->whereIn('country_id', $countryIds);
    }

    if (!empty($nameSearch)) {
      $userIds = User::where('name', 'LIKE', "%{$nameSearch}%")
        ->pluck('id')
        ->toArray();
      $query->whereIn('user_id', $userIds);
    }

    if (!empty($categorySearch)) {
      $query->where('owner_category_id', $categorySearch);
    }

    $owners = $query->latest()->get();

    foreach ($owners as $owner) {
      $category = Owner_category::query()->where('id', $owner->owner_category_id)->first();
      $country = Country::query()->where('id', $owner->country_id)->first();
      $user = User::query()->where('id', $owner->user_id)->first();

      $ownerData = [
        'owner' => $owner,
        'user' => $user,
        'category' => $category->name,
        'country' => $country->name
      ];

      switch ($owner->owner_category_id) {
        case 1: // Accommodation
          $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
          if ($accommodation) {
            $accommodation_type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->first();
            $ownerData['details'] = [
              'accommodation' => $accommodation,
              'accommodation_type' => $accommodation_type ? $accommodation_type->name : null
            ];
          }
          break;
        case 2: // Air Line
          $air_line = Air_line::query()->where('owner_id', $owner->id)->first();
          $ownerData['details'] = $air_line;
          break;
        case 3: // Tourism Company
          $tourism = Tourism_company::query()->where('owner_id', $owner->id)->first();
          $ownerData['details'] = $tourism;
          break;
        case 4: // Vehicle Owner
          $vehicle_owner = Vehicle_owner::query()->where('owner_id', $owner->id)->first();
          $ownerData['details'] = $vehicle_owner;
          break;
        case 5: // Activity Owner
          $activity_owner = Activity_owner::query()->where('owner_id', $owner->id)->first();
          $ownerData['details'] = $activity_owner;
          break;
      }

      $data[] = $ownerData;
    }

    if ($owners->isNotEmpty()) {
      return ['data' => $data];
    } else {
      return ['message' => 'No Result'];
    }
  }

  public function getAllPackages()
  {
    return Package::with('tourism_company')->get();
  }

  public function getPackage($id)
  {
    $package = Package::with([
      'package_element.package_element_picture',
      'tourism_company.owner.user'
    ])->find($id);

    if (!$package) {
      return null;
    }

    if ($package->package_picture) {
      $package->package_picture = asset('storage/' . $package->package_picture);
    }

    if ($package->package_element) {
      foreach ($package->package_element as $element) {
        if ($element->package_element_picture) {
          foreach ($element->package_element_picture as $pic) {
            if ($pic->picture_path) {
              $pic->picture_path = asset('storage/' . $pic->picture_path);
            }
          }
        }
      }
    }

    return $package;
  }


  public function togglePayByPoint($id)
  {
    $package = Package::find($id);

    if (!$package) {
      return null;
    }

    $package->payment_by_points = !$package->payment_by_points;
    $package->save();

    return $package;
  }

  public function getAllUsers()
  {
    $users = User::query()
      ->where('role_id', 3)
      ->get();

    return $users;
  }

  public function filterUsers($name)
  {
    return User::query()
      ->where('name', 'like', '%' . $name . '%')
      ->where('role_id', 3)
      ->get();
  }

  public function filterSubAdmins($name)
  {
    return User::query()
      ->where('name', 'like', '%' . $name . '%')
      ->where('role_id', 2)
      ->get();
  }

  public function createSubAdmin($id)
  {
    $user = User::find($id);

    if (!$user) {
      return null; // نخلي الكنترولر يتعامل مع حالة عدم الوجود
    }

    $user->role_id = 2;
    $user->save();

    return $user;
  }

  public function getAllSubAdmins()
  {
    return User::where('role_id', 2)->get();
  }
  public function removeSubAdmin($id)
  {
    $user = User::find($id);

    if (!$user) {
      return null; // نخلي الكنترولر يتعامل مع حالة عدم الوجود
    }

    $user->role_id = 3; // إرجاعه لدور المستخدم العادي
    $user->save();

    return $user;
  }

  public function getAllActivity()
  {
    return Activity::all();
  }

  public function addActivity($name)
  {
    if (Activity::where('name', $name)->exists()) {
      return [
        'exists' => true,
        'activity' => null
      ];
    }

    $activity = new Activity();
    $activity->name = $name;
    $activity->save();

    return [
      'exists' => false,
      'activity' => $activity
    ];
  }

  public function deleteActivity($id)
  {
    $activity = Activity::find($id);

    if (!$activity) {
      return null;
    }

    $activity->delete();

    return true;
  }

  public function addCategory($name)
    {
        if (Owner_category::where('name', $name)->exists()) {
            return [
                'exists' => true,
                'category' => null
            ];
        }

        $category = new Owner_category();
        $category->name = $name;
        $category->save();

        return [
            'exists' => false,
            'category' => $category
        ];
    }
}
