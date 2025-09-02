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
use App\Models\Plan_type;
use App\Models\Room;
use App\Models\Room_picture;
use App\Models\Service;
use App\Models\Tourism_company;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vehicle_owner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeneralTaskService
{
  // public function who_am_i()
  // {
  //   $user = Auth::user();
  //   $owner = Owner::query()->where('user_id', $user->id)->first();
  //   $data = [];
  //   $role = Owner_category::query()->where('id', $owner->owner_category_id)->first();
  //   $data['role'] = $role->name;

  //   if ($role->name == 'Accommodation') {
  //     $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
  //     $type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->first();
  //     $data['type'] = $type->name;
  //   } elseif ($role->name == 'Activity Owner') {
  //     $activity_owner = Activity_owner::query()->where('owner_id', $owner->id)->first();
  //     $activity = Activity::query()->where('id', $activity_owner->activity_id)->first();
  //     $data['type'] = $activity->name;
  //   }

  //   return $data;
  // }

  public function get_all_owners_categories()
  {
    return Owner_category::query()->get();
  }

  public function get_all_countries()
  {
    return Country::query()->get();
  }

  public function get_all_accommodation_types()
  {
    return Accommodation_type::query()->get();
  }

  public function get_all_car_types()
  {
    return Car_type::query()->get();
  }

  public function get_all_plane_types()
  {
    return Plan_type::query()->get();
  }

  public function get_all_services()
  {
    return Service::query()->get();
  }

  public function show_profile()
  {
    $user = Auth::user();
    $id = $user->id;
    $data = [];

    if ($user['role_id'] == 3) {
      $data['user'] = $user;
      return $data;
    }
    $owner = Owner::query()->where('user_id', $id)->first();
    $data['owner'] = $owner;
    $owner['user'] = User::query()->where('id', $owner->user_id)->first();
    $country = Country::query()->where('id', $owner->country_id)->first();
    $category = Owner_category::query()->where('id', $owner->owner_category_id)->select('name')->first();
    $owner['country'] = $country->name;
    $owner['countryId'] = $country->id;
    $owner['category'] = $category->name;

    $category_id = $owner->owner_category_id;
    $data['pictures'] = Picture::query()->where('owner_id', $owner->id)->get();
    $ffs = Owner_service::query()->where('owner_id', $owner->id)->get();
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
      $data['details'] = $activity_owner;
    }

    return $data;
  }

  public function add_picture($request)
  {
    $user = Auth::user();

    if ($user['role_id'] != 4) {
      return ['message' => 'Authorization required'];
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();

    $image = $request->file('picture');
    $imageName = time() . '_' . $image->getClientOriginalName();
    $image->storeAs('public/plane_shape_diagram', $imageName);
    $publicPath = 'storage/plane_shape_diagram/' . $imageName;

    $plane = Picture::query()->create([
      'owner_id' => $owner->id,
      'reference' => $publicPath,
    ]);

    return ['message' => 'new picture added successfully'];
  }

  public function delete_picture($id)
  {
    $user = Auth::user();

    if ($user['role_id'] != 4) {
      return ['message' => 'Authorization required'];
    }
    Picture::query()->where('id', $id)->delete();

    return ['message' => 'this picture deleted successfully'];
  }

  public function add_service($request)
  {
    $user = Auth::user();
    Log::info('AddService request', ['user' => $user, 'services' => $request->services]);

    if ($user['role_id'] != 4) {
      return ['message' => 'Authorization required'];
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();

    // validation done in GeneralTaskRequest
    $services = $request->input('services');

    foreach ($services as $serviceName) {
      $service = Service::firstOrCreate([
        'name' => $serviceName
      ]);

      $owner_service = Owner_service::query()
        ->where('service_id', $service->id)
        ->where('owner_id', $owner->id)
        ->first();

      if (!isset($owner_service)) {
        Owner_service::query()->create([
          'service_id' => $service->id,
          'owner_id' => $owner->id,
        ]);
      }
    }

    return ['message' => 'new service added successfully'];
  }

  public function delete_service($id)
  {
    $user = Auth::user();

    if ($user['role_id'] != 4) {
      return ['message' => 'Authorization required'];
    }

    $owner = Owner::query()->where('user_id', $user->id)->first();
    Owner_service::query()
      ->where('service_id', $id)
      ->where('owner_id', $owner->id)
      ->delete();

    return ['message' => 'this service deleted successfully'];
  }

  public function edit_profile($request, $remainingPictureIds, $deletedPictureIds, $images)
  {
    $user = User::find(Auth::id());
    $owner = Owner::query()->where('user_id', $user->id)->first();

    $user->update([
      'name' => $request['name'],
      'email' => $request['email'],
      'phone_number' => $request['phone_number'],
    ]);
    $owner->update([
      'description' => $request['description'],
      'location' => $request['location'],
      'country_id' => $request['country_id'],
    ]);
    if (!empty($deletedPictureIds)) {
      $deletedImages = Picture::whereIn('id', $deletedPictureIds)->get();
      foreach ($deletedImages as $deletedImage) {
        $fileName = basename($deletedImage->room_picture);
        Storage::disk('public')->delete("images/{$fileName}");
        $deletedImage->delete();
      }
    }

    $allOldImages = Picture::where('owner_id', $owner->id)->get();
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

    if (!empty($images)) {
      foreach ($images as $image) {
        $imagePath = $image->store('images', 'public');
        Picture::query()->create([
          'owner_id' => $owner->id,
          'reference' => 'storage/' . $imagePath
        ]);
      }
    }

    return [
      'message' => 'Profile updated successfully'
    ];
  }
}
