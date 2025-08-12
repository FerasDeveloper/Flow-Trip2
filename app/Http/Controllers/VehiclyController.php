<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleOwnerRequest;
use App\Models\Car_picture;
use App\Models\Vehicle;
use App\Services\VehicleOwnerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehiclyController extends Controller
{

  protected $vehicleOwnerService;

  public function __construct(VehicleOwnerService $vehicleOwnerService)
  {
    $this->vehicleOwnerService = $vehicleOwnerService;
  }
  // public function createVehicly(Request $request)
  // {
  //     $user = Auth::user();
  //     $owner = $user->Owner;
  //     $vehicleOwner = $owner->Vehicle_owner;

  //     if (!$vehicleOwner) {
  //         return response()->json(['message' => 'Vehicle owner not found for this user'], 404);
  //     }

  //     $validatedData = $request->validate([
  //         'car_type_id' => 'required|integer',
  //         'car_discription' => 'nullable|string|max:255',
  //         'people_count' => 'required',
  //         'name' => 'required'
  //     ]);

  //     $vehicle = Vehicle::create([
  //         'vehicle_owner_id' => $vehicleOwner->id,
  //         'car_type_id' => $validatedData['car_type_id'],
  //         'car_discription' => $validatedData['car_discription'] ?? '',
  //         'people_count' => $validatedData['people_count'],
  //         'name' => $validatedData['name'],
  //     ]);

  //     return response()->json([
  //         'message' => 'Vehicle created successfully',
  //         'vehicle' => $vehicle,
  //     ], 201);
  // }


  public function createVehicly(VehicleOwnerRequest $request)
  {
    $data = $request->validated();
    $result = $this->vehicleOwnerService->createVehicly($data);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], 404);
    }

    return response()->json([
      'message' => 'Vehicle created successfully',
      'vehicle' => $result,
    ], 201);
  }

  // public function editVehicly(Request $request, $id)
  // {
  //     $request->validate([
  //         'car_discription' => 'nullable|string|max:255',
  //         'people_count' => 'nullable|integer|min:1', 
  //     ]);

  //     $vehicle = Vehicle::findOrFail($id);

  //     if ($request->has('car_discription')) {
  //         $vehicle->car_discription = $request->car_discription;
  //     }

  //     if ($request->has('people_count')) {
  //         $vehicle->people_count = $request->people_count;
  //     }

  //     $vehicle->save();

  //     return response()->json([
  //         'message' => 'The vehicle has been updated successfully',
  //         'vehicle' => $vehicle,
  //     ]);
  // }


  public function editVehicly(VehicleOwnerRequest $request, $id)
  {
    $data = $request->validated();
    $result = $this->vehicleOwnerService->editVehicly($data, $id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], 404);
    }

    return response()->json([
      'message' => 'The vehicle has been updated successfully',
      'vehicle' => $result,
    ], 200);
  }

  // public function deleteVehicly($id)
  // {
  //   $vehicle = Vehicle::find($id);

  //   if (!$vehicle) {
  //     return response()->json([
  //       'message' => 'Vehicle not found',
  //     ], 404);
  //   }

  //   try {
  //     $vehicle->delete();

  //     return response()->json([
  //       'message' => 'Vehicle deleted successfully',
  //     ], 200);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'Failed to delete the vehicle',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }



  public function deleteVehicly(VehicleOwnerRequest $request, $id)
  {
    $result = $this->vehicleOwnerService->deleteVehicly($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => $result['message'],
    ], 200);
  }

  // public function createPictureCar(Request $request)
  // {
  //   $request->validate([
  //     'vehicle_id' => 'required|exists:vehicles,id',
  //     'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
  //   ]);

  //   $pictureFile = $request->file('picture');
  //   $path = $pictureFile->store('car_pictures', 'public');

  //   $picture = Car_picture::create([
  //     'vehicle_id' => $request->vehicle_id,
  //     'picture_path' => $path,
  //   ]);

  //   return response()->json([
  //     'message' => 'Car picture uploaded successfully',
  //     'data' => $picture,
  //   ], 201);
  // }


  public function createPictureCar(VehicleOwnerRequest $request)
  {
    $data = $request->validated();
    $result = $this->vehicleOwnerService->createPictureCar($data);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 400);
    }

    return response()->json([
      'message' => 'Car picture uploaded successfully',
      'data' => $result,
    ], 201);
  }

  // public function getAllPicture($id)
  // {
  //   $pictures = Car_picture::where('vehicle_id', $id)->get();

  //   if ($pictures->isEmpty()) {
  //     return response()->json([
  //       'message' => 'No pictures found for this vehicle.',
  //       'images' => []
  //     ], 404);
  //   }

  //   $imageData = $pictures->map(function ($pic) {
  //     return [
  //       'id' => $pic->id,
  //       'path' => asset('storage/' . $pic->picture_path),
  //     ];
  //   });

  //   return response()->json([
  //     'message' => 'Car pictures retrieved successfully.',
  //     'images' => $imageData,
  //   ], 200);
  // }


  public function getAllPicture($id)
  {
    $result = $this->vehicleOwnerService->getAllPicture($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
        'images' => [],
      ], $result['status'] ?? 404);
    }

    return response()->json([
      'message' => 'Car pictures retrieved successfully.',
      'images' => $result,
    ], 200);
  }

  // public function deletePictureCar($id)
  // {
  //   $picture = Car_picture::find($id);

  //   if (!$picture) {
  //     return response()->json([
  //       'message' => 'Picture not found.',
  //     ], 404);
  //   }

  //   try {
  //     $relativePath = str_replace('storage/', '', parse_url($picture->picture_path, PHP_URL_PATH));

  //     if (Storage::disk('public')->exists($relativePath)) {
  //       Storage::disk('public')->delete($relativePath);
  //     }

  //     $picture->delete();

  //     return response()->json([
  //       'message' => 'Picture deleted successfully.',
  //     ], 200);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'Failed to delete the picture.',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }

  public function deletePictureCar(int $id)
  {
    $result = $this->vehicleOwnerService->deletePictureCar($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => $result['message'],
    ], 200);
  }


  // public function getAllViclyForuser($id)
  // {
  //   try {
  //     $vehicles = DB::table('owners')
  //       ->join('vehicle_owners', 'owners.id', '=', 'vehicle_owners.owner_id')
  //       ->join('vehicles', 'vehicles.vehicle_owner_id', '=', 'vehicle_owners.id')
  //       ->join('car_types', 'vehicles.car_type_id', '=', 'car_types.id')
  //       ->leftJoin('car_pictures', 'vehicles.id', '=', 'car_pictures.vehicle_id')
  //       ->select(
  //         'vehicles.id as vehicle_id',
  //         'car_types.name as car_type_name',
  //         'vehicles.car_discription',
  //         'vehicles.name',
  //         'vehicles.people_count',
  //         'car_pictures.picture_path',
  //         'owners.description as owner_description',
  //         'owners.location'
  //       )
  //       ->where('owners.user_id', $id)
  //       ->get();

  //     if ($vehicles->isEmpty()) {
  //       return response()->json([
  //         'message' => 'No vehicles found for this user.'
  //       ], 404);
  //     }

  //     $vehicles->transform(function ($vehicle) {
  //       $vehicle->picture_url = $vehicle->picture_path
  //         ? asset('storage/' . $vehicle->picture_path)
  //         : null;
  //       unset($vehicle->picture_path);
  //       return $vehicle;
  //     });


  //     return response()->json([
  //       'message' => 'Vehicles retrieved successfully.',
  //       'data' => $vehicles
  //     ], 200);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'An error occurred while fetching vehicles.',
  //       'error' => $e->getMessage()
  //     ], 500);
  //   }
  // }

  public function getAllViclyForuser($id)
  {
    $result = $this->vehicleOwnerService->getAllViclyForuser($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (empty($result)) {
      return response()->json([
        'message' => 'No vehicles found for this user.',
        'data' => [],
      ], 404);
    }

    return response()->json([
      'message' => 'Vehicles retrieved successfully.',
      'data' => $result,
    ], 200);
  }

  // public function getVehicleById($id)
  // {
  //   try {
  //     $vehicle = Vehicle::with([
  //       'car_type'
  //     ])->find($id);

  //     if (!$vehicle) {
  //       return response()->json([
  //         'message' => 'Vehicle not found.'
  //       ], 404);
  //     }

  //     $data = [
  //       'vehicle_id' => $vehicle->id,
  //       'car_type_name' => $vehicle->car_type->name ?? null,
  //       'car_discription' => $vehicle->car_discription,
  //       'people_count' => $vehicle->people_count,
  //       'name' => $vehicle->name,
  //       'location' => $vehicle->vehicle_owner->owner->location ?? null
  //     ];

  //     return response()->json([
  //       'message' => 'Vehicle details retrieved successfully.',
  //       'data' => $data
  //     ], 200);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'An error occurred while retrieving the vehicle details.',
  //       'error' => $e->getMessage()
  //     ], 500);
  //   }
  // }

  public function getVehicleById($id)
  {
    $result = $this->vehicleOwnerService->getVehicleById($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (empty($result)) {
      return response()->json([
        'message' => 'Vehicle not found.',
        'data' => [],
      ], 404);
    }

    return response()->json([
      'message' => 'Vehicle details retrieved successfully.',
      'data' => $result,
    ], 200);
  }
}
