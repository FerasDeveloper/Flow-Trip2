<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeneralTaskController extends Controller
{

  public function who_am_i(){
    
    $user = Auth::user();
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $data = [];
    $role = Owner_category::query()->where('id',$owner->owner_category_id)->first();
    $data['role'] = $role->name;

    if($role->name == 'Accommodation'){
      $accommodation = Accommodation::query()->where('owner_id', $owner->id)->first();
      $type = Accommodation_type::query()->where('id', $accommodation->accommodation_type_id)->first();
      $data['type'] = $type->name;
    }
    elseif($role->name == 'Activity Owner'){
      $activity_owner = Activity_owner::query()->where('owner_id', $owner->id)->first();
      $activity = Activity::query()->where('id', $activity_owner->activity_id)->first();
      $data['type'] = $activity->name;
    }

    return response()->json([
      'data' => $data
    ]);

  }

  public function get_all_owners_categories()
  {
    $owners_category = Owner_category::query()->get();
    return response()->json([
      'owners_categories' => $owners_category
    ]);
  }

  public function get_all_countries()
  {
    $country = Country::query()->get();
    return response()->json([
      'countries' => $country
    ]);
  }

  public function get_all_accommodation_types()
  {
    $accommodation_type = Accommodation_type::query()->get();
    return response()->json([
      'accommodation_types' => $accommodation_type
    ]);
  }

  public function get_all_car_types()
  {
    $car_type = Car_type::query()->get();
    return response()->json([
      'car_types' => $car_type
    ]);
  }

  public function get_all_plane_types()
  {
    $plane_type = Plan_type::query()->get();
    return response()->json([
      'plane_types' => $plane_type
    ]);
  }

  public function get_all_services()
  {
    $service = Service::query()->get();
    return response()->json([
      'services' => $service
    ]);
  }


  public function show_profile()
  {
    $user = Auth::user();
    $id = $user->id;

    if ($user['role_id'] == 3) {
      $data['user'] = $user;
      return response()->json($data);
    }

    $data = [];
    $owner = Owner::query()->where('user_id', $id)->first();
    $data['owner'] = $owner;
    $owner['user'] = User::query()->where('id', $owner->user_id)->first();
    $country = Country::query()->where('id', $owner->country_id)->select('name')->first();
    $category = Owner_category::query()->where('id', $owner->owner_category_id)->select('name')->first();
    $owner['country'] = $country->name;
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

    return response()->json($data);
  }


  public function add_picture(Request $request)
  {
    $user = Auth::user();

    if ($user['role_id'] != 4) {
      return response()->json([
        'message' => 'Authorization required'
      ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();

    $request->validate([
      'picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);
    $image = $request->file('picture');
    $imageName = time() . '_' . $image->getClientOriginalName();
    $image->storeAs('public/plane_shape_diagram', $imageName);
    $publicPath = 'storage/plane_shape_diagram/' . $imageName;

    $plane = Picture::query()->create([
      'owner_id' => $owner->id,
      'reference' => $publicPath,
    ]);

    return response()->json([
      'message' => 'new picture added successfully',
    ]);
  }


  public function delete_picture($id)
  {
    $user = Auth::user();

    if ($user['role_id'] != 4) {
      return response()->json([
        'message' => 'Authorization required'
      ]);
    }
    $picture = Picture::query()->where('id', $id)->delete();

    return response()->json([
      'message' => 'this picture deleted successfully',
    ]);
  }


  public function add_service(Request $request)
  {
    $user = Auth::user();
    Log::info('AddService request', ['user' => $user, 'services' => $request->services]);

    if ($user['role_id'] != 4) {
      return response()->json([
        'message' => 'Authorization required'
      ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();

    $request->validate([
      'services' => 'required',
    ]);

    $services = $request->input('services');

    foreach($services as $service){
      $service = Service::firstOrCreate([
        'name' => $service
      ]);

      $owner_service = Owner_service::query()->where('service_id', $service->id)->where('owner_id', $owner->id)->first();
      if(!isset($owner_service)){
        Owner_service::query()->create([
          'service_id' => $service->id,
          'owner_id' => $owner->id,
        ]);
      }
    }

    return response()->json([
      'message' => 'new service added successfully',
    ]);
  }



  public function delete_service($id)
  {
    $user = Auth::user();

    if ($user['role_id'] != 4) {
      return response()->json([
        'message' => 'Authorization required'
      ]);
    }
    
    $owner = Owner::query()->where('user_id', $user->id)->first();
    Owner_service::query()->where('service_id', $id)
      ->where('owner_id', $owner->id)->delete();

    return response()->json([
      'message' => 'this service deleted successfully',
    ]);
  }

  public function edit_profile(Request $request){
    $user = User::find(Auth::id());
    $owner = Owner::query()->where('user_id', $user->id)->first();

    $user->update([
      'name' => $request->name,
      'email' => $request->email,
      'phone_number' => $request->phone_number,
    ]);
    $owner->update([
      'description' => $request->description,
      'location' => $request->location,
      'country_id' => $request->country_id,
    ]);

    $remainingPictureIds = [];
    $deletedPictureIds = [];

    if ($request->has('remaining_picture_ids')) {
        $remainingPictureIds = json_decode($request->remaining_picture_ids, true);
    }

    if ($request->has('deleted_picture_ids')) {
        $deletedPictureIds = json_decode($request->deleted_picture_ids, true);
    }

    if (!empty($deletedPictureIds)) {
        $deletedImages = Picture::whereIn('id', $deletedPictureIds)->get();
        foreach ($deletedImages as $deletedImage) {
            $fileName = basename($deletedImage->room_picture);
            Storage::disk('public')->delete("images/{$fileName}");
            $deletedImage->delete();
        }
    }

    $allOldImages = Picture::where('owner_id', $owner->id)->get();
    foreach ($allOldImages as $oldImage) {
        if (!in_array($oldImage->id, $remainingPictureIds)) {
            $fileName = basename($oldImage->room_picture);
            Storage::disk('public')->delete("images/{$fileName}");
            $oldImage->delete();
        }
    }

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $imagePath = $image->store('images', 'public');
            Picture::query()->create([
                'owner_id' => $owner->id,
                'reference' => 'storage/' . $imagePath
            ]);
        }
    }

    return response()->json([
        'message' => 'Profile updated successfully'
    ]);
  }
  
}
