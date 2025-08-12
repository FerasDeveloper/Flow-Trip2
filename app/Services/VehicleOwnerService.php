<?php

namespace App\Services;

use App\Models\Car_picture;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleOwnerService
{
  public function createVehicly(array $data)
  {
    $user = Auth::user();
    $owner = $user->Owner;
    $vehicleOwner = $owner->Vehicle_owner;

    if (!$vehicleOwner) {
      return ['error' => 'Vehicle owner not found for this user'];
    }

    $vehicle = Vehicle::create([
      'vehicle_owner_id' => $vehicleOwner->id,
      'car_type_id' => $data['car_type_id'],
      'car_discription' => $data['car_discription'] ?? '',
      'people_count' => $data['people_count'],
      'name' => $data['name'],
    ]);

    return $vehicle;
  }

  public function editVehicly(array $data, $id)
  {
    $vehicle = Vehicle::find($id);

    if (!$vehicle) {
      return ['error' => 'Vehicle not found.'];
    }

    if (isset($data['car_discription'])) {
      $vehicle->car_discription = $data['car_discription'];
    }

    if (isset($data['people_count'])) {
      $vehicle->people_count = $data['people_count'];
    }

    $vehicle->save();

    return $vehicle;
  }

  public function deleteVehicly($id)
  {
    $vehicle = Vehicle::find($id);

    if (!$vehicle) {
      return [
        'error' => 'Vehicle not found',
        'status' => 404,
      ];
    }

    try {
      $vehicle->delete();

      return [
        'message' => 'Vehicle deleted successfully',
      ];
    } catch (\Exception $e) {
      return [
        'error' => 'Failed to delete the vehicle',
        'status' => 500,
      ];
    }
  }
  public function createPictureCar(array $data)
  {
    if (!isset($data['picture'])) {
      return [
        'error' => 'No picture file provided.',
        'status' => 400,
      ];
    }

    $file = $data['picture'];
    $path = $file->store('car_pictures', 'public');

    $picture = Car_picture::create([
      'vehicle_id' => $data['vehicle_id'],
      'picture_path' => $path,
    ]);

    return [
      'id' => $picture->id,
      'vehicle_id' => $picture->vehicle_id,
      'path' => asset('storage/' . $path),
    ];
  }
  public function getAllPicture($vehicle_id)
  {
    $vehicleExists = Vehicle::where('id', $vehicle_id)->exists();

    if (!$vehicleExists) {
      return [
        'error' => 'Vehicle not found.',
        'status' => 404,
      ];
    }

    $pictures = Car_picture::where('vehicle_id', $vehicle_id)->get();

    if ($pictures->isEmpty()) {
      return [
        'error' => 'No pictures found for this vehicle.',
        'status' => 404,
      ];
    }

    return $pictures->map(function ($pic) {
      return [
        'id' => $pic->id,
        'path' => asset('storage/' . $pic->picture_path),
      ];
    });
  }

  public function deletePictureCar($id)
  {
    $picture = Car_picture::find($id);

    if (!$picture) {
      return [
        'error' => 'Picture not found.',
        'status' => 404,
      ];
    }

    try {
      $relativePath = str_replace('storage/', '', parse_url(asset('storage/' . $picture->picture_path), PHP_URL_PATH));

      if (Storage::disk('public')->exists($relativePath)) {
        Storage::disk('public')->delete($relativePath);
      }

      $picture->delete();

      return [
        'message' => 'Picture deleted successfully.',
      ];
    } catch (\Exception $e) {
      return [
        'error' => 'Failed to delete the picture.',
        'status' => 500,
      ];
    }
  }
  public function getAllViclyForuser($user_id)
  {
    try {
      $vehicles = DB::table('owners')
        ->join('vehicle_owners', 'owners.id', '=', 'vehicle_owners.owner_id')
        ->join('vehicles', 'vehicles.vehicle_owner_id', '=', 'vehicle_owners.id')
        ->join('car_types', 'vehicles.car_type_id', '=', 'car_types.id')
        ->leftJoin('car_pictures', 'vehicles.id', '=', 'car_pictures.vehicle_id')
        ->select(
          'vehicles.id as vehicle_id',
          'car_types.name as car_type_name',
          'vehicles.car_discription',
          'vehicles.name',
          'vehicles.people_count',
          'car_pictures.picture_path',
          'owners.description as owner_description',
          'owners.location'
        )
        ->where('owners.user_id', $user_id)
        ->get();

      if ($vehicles->isEmpty()) {
        return [];
      }

      return $vehicles->transform(function ($vehicle) {
        $vehicle->picture_url = $vehicle->picture_path
          ? asset('storage/' . $vehicle->picture_path)
          : null;
        unset($vehicle->picture_path);
        return $vehicle;
      });
    } catch (\Exception $e) {
      return [
        'error' => 'An error occurred while fetching vehicles.',
        'status' => 500,
      ];
    }
  }
  public function getVehicleById($vehicle_id)
  {
    try {
      $vehicle = Vehicle::with([
        'car_type',
        'vehicle_owner.owner',
      ])->find($vehicle_id);

      if (!$vehicle) {
        return [];
      }

      return [
        'vehicle_id' => $vehicle->id,
        'car_type_name' => $vehicle->car_type->name ?? null,
        'car_discription' => $vehicle->car_discription,
        'people_count' => $vehicle->people_count,
        'name' => $vehicle->name,
        'location' => $vehicle->vehicle_owner->owner->location ?? null,
      ];
    } catch (\Exception $e) {
      return [
        'error' => 'An error occurred while retrieving the vehicle details.',
        'status' => 500,
      ];
    }
  }
}
