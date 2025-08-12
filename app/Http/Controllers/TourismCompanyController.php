<?php

namespace App\Http\Controllers;

use App\Http\Requests\TourismCompanyRequist;
use App\Models\Owner;
use App\Models\Package;
use App\Models\Package_element;
use App\Models\Package_element_picture;
use App\Models\Tourism_company;
use App\Models\User_package;
use App\Services\TourismCompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TourismCompanyController extends Controller
{
  protected $tourismService;

  public function __construct(TourismCompanyService $tourismService)
  {
    $this->tourismService = $tourismService;
  }

  // public function createPackage(Request $request)
  // {
  //   $request->validate([
  //     'discription' => 'required|string',
  //     'total_price' => 'required|numeric',
  //     'checked' => 'required|boolean',
  //     'package_picture' => 'nullable|file|image',
  //   ]);
  //   $user = Auth::user();
  //   $owner = Owner::where('user_id', $user->id)->first();

  //   if (!$owner) {
  //     return response()->json([
  //       'message' => 'Owner not found for the current user.'
  //     ], 404);
  //   }

  //   $company = Tourism_company::where('owner_id', $owner->id)->first();

  //   if (!$company) {
  //     return response()->json([
  //       'message' => 'Tourism company not found for the current owner.'
  //     ], 404);
  //   }
  //   $imagePath = null;
  //   if ($request->hasFile('package_picture')) {
  //     $imagePath = $request->file('package_picture')->store('packages', 'public');
  //   }
  //   $package = Package::create([
  //     'tourism_company_id' => $company->id,
  //     'discription' => $request->discription,
  //     'total_price' => $request->total_price,
  //     'checked' => $request->checked,
  //     'payment_by_points' => 0,
  //     'package_picture' => $imagePath,
  //   ]);

  //   return response()->json([
  //     'message' => 'Package created successfully.',
  //     'data' => $package,
  //   ], 201);
  // }



  public function createPackage(Request $request)
  {
    $request->validate([
      'discription' => 'required|string',
      'total_price' => 'required|numeric',
      'checked' => 'required|boolean',
      'package_picture' => 'nullable|file|image',
    ]);

    $result = $this->tourismService->createPackage($request);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => 'Package created successfully.',
      'data' => $result['data'],
    ], 201);
  }

  // public function editPackage(Request $request, $id)
  // {
  //   $request->validate([
  //     'discription' => 'nullable|string',
  //     'total_price' => 'nullable|numeric',
  //     'checked' => 'nullable|boolean',
  //     'package_picture' => 'nullable|file|image',
  //   ]);

  //   $package = Package::find($id);

  //   if (!$package) {
  //     return response()->json([
  //       'message' => 'Package not found.',
  //     ], 404);
  //   }

  //   if ($request->has('discription')) {
  //     $package->discription = $request->discription;
  //   }

  //   if ($request->has('total_price')) {
  //     $package->total_price = $request->total_price;
  //   }

  //   if ($request->has('checked')) {
  //     $package->checked = $request->checked;
  //   }

  //   if ($request->hasFile('package_picture')) {
  //     if ($package->package_picture && Storage::disk('public')->exists($package->package_picture)) {
  //       Storage::disk('public')->delete($package->package_picture);
  //     }

  //     $newPath = $request->file('package_picture')->store('packages', 'public');
  //     $package->package_picture = $newPath;
  //   }

  //   $package->save();

  //   return response()->json([
  //     'message' => 'Package updated successfully.',
  //     'data' => $package,
  //   ], 200);
  // }



  public function editPackage(TourismCompanyRequist $request, $id)
  {
    $data = $request->validated();
    $result = $this->tourismService->editPackage($data, $id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => 'Package updated successfully.',
      'data' => $result['data'],
    ], 200);
  }

  // public function deletePackage($id)
  // {
  //   $package = Package::find($id);

  //   if (!$package) {
  //     return response()->json([
  //       'message' => 'Package not found.',
  //     ], 404);
  //   }

  //   try {
  //     if ($package->package_picture && Storage::disk('public')->exists($package->package_picture)) {
  //       Storage::disk('public')->delete($package->package_picture);
  //     }

  //     $package->delete();

  //     return response()->json([
  //       'message' => 'Package deleted successfully.',
  //     ], 200);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'Failed to delete the package.',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }



  public function deletePackage(TourismCompanyRequist $request, $id)
  {
    $result = $this->tourismService->deletePackage($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => 'Package deleted successfully.',
    ], 200);
  }


  // public function getPackagesfortourism()
  // {
  //   $user = Auth::user();

  //   $owner = Owner::where('user_id', $user->id)->first();

  //   if (!$owner) {
  //     return response()->json([
  //       'message' => 'Owner not found for this user.',
  //     ], 404);
  //   }

  //   $company = Tourism_company::where('owner_id', $owner->id)->first();

  //   if (!$company) {
  //     return response()->json([
  //       'message' => 'Tourism company not found for this owner.',
  //     ], 404);
  //   }

  //   $packages = Package::where('tourism_company_id', $company->id)->get();

  //   $packageData = $packages->map(function ($package) {
  //     return [
  //       'id' => $package->id,
  //       'description' => $package->discription,
  //       'total_price' => $package->total_price,
  //       'checked' => $package->checked,
  //       'payment_by_points' => $package->payment_by_points,
  //       'picture' => $package->package_picture
  //         ? asset('storage/' . $package->package_picture)
  //         : null,
  //     ];
  //   });

  //   return response()->json([
  //     'message' => 'Packages retrieved successfully.',
  //     'data' => $packageData,
  //   ], 200);
  // }


  public function getPackagesfortourism(TourismCompanyRequist $request)
  {
    $result = $this->tourismService->getPackagesfortourism();

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => 'Packages retrieved successfully.',
      'data' => $result,
    ], 200);
  }


  // public function addPackageElement(Request $request, $id)
  // {
  //   $request->validate([
  //     'name' => 'required|string',
  //     'type' => 'required|string',
  //     'discription' => 'required|string',
  //     'pictures.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
  //   ]);

  //   try {
  //     $element = Package_element::create([
  //       'name' => $request->name,
  //       'type' => $request->type,
  //       'discription' => $request->discription,
  //       'package_id' => $id,
  //     ]);

  //     if ($request->hasFile('pictures')) {
  //       foreach ($request->file('pictures') as $picture) {
  //         $path = $picture->store('package_elements', 'public');

  //         Package_element_picture::create(attributes: [
  //           'package_element_id' => $element->id,
  //           'picture_path' => $path,
  //         ]);
  //       }
  //     }

  //     return response()->json([
  //       'message' => 'Package element added successfully.',
  //       'element' => $element,
  //     ], 201);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'Failed to add package element.',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }



  public function addPackageElement(TourismCompanyRequist $request, $id)
  {
    $data = $request->validated();
    $result = $this->tourismService->addPackageElement($data, $id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => 'Failed to add package element.',
        'error' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => 'Package element added successfully.',
      'element' => $result,
    ], 201);
  }


  // public function editPackageElement(Request $request, $id)
  // {
  //   $request->validate([
  //     'name' => 'nullable|string',
  //     'type' => 'nullable|string',
  //     'discription' => 'nullable|string',
  //   ]);

  //   $element = Package_element::find($id);

  //   if (!$element) {
  //     return response()->json([
  //       'message' => 'Package element not found.',
  //     ], 404);
  //   }

  //   try {
  //     if ($request->has('name')) $element->name = $request->name;
  //     if ($request->has('type')) $element->type = $request->type;
  //     if ($request->has('discription')) $element->discription = $request->discription;

  //     $element->save();

  //     return response()->json([
  //       'message' => 'Package element updated successfully.',
  //       'element' => $element,
  //     ], 200);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'Failed to update package element.',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }


  public function editPackageElement(TourismCompanyRequist $request, $id)
  {
    $data = $request->validated();
    $result = $this->tourismService->editPackageElement($data, $id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => 'Failed to update package element.',
        'error' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (isset($result['not_found'])) {
      return response()->json([
        'message' => $result['not_found'],
      ], 404);
    }

    return response()->json([
      'message' => 'Package element updated successfully.',
      'element' => $result,
    ], 200);
  }

  // public function deletePackageElement($id)
  // {
  //   $element = Package_element::find($id);

  //   if (!$element) {
  //     return response()->json([
  //       'message' => 'Package element not found.',
  //     ], 404);
  //   }

  //   try {
  //     $pictures = $element->package_element_picture;

  //     foreach ($pictures as $pic) {
  //       if (Storage::disk('public')->exists($pic->picture_path)) {
  //         Storage::disk('public')->delete($pic->picture_path);
  //       }
  //       $pic->delete();
  //     }

  //     $element->delete();

  //     return response()->json([
  //       'message' => 'Package element and its pictures deleted successfully.',
  //     ], 200);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'message' => 'Failed to delete the package element.',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }


  public function deletePackageElement($id)
  {
    $result = $this->tourismService->deletePackageElement($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => 'Failed to delete the package element.',
        'error' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (isset($result['not_found'])) {
      return response()->json([
        'message' => $result['not_found'],
      ], 404);
    }

    return response()->json([
      'message' => $result['message'],
    ], 200);
  }


  // public function getPictureForElement($id)
  // {
  //   $element = Package_element::find($id);

  //   if (!$element) {
  //     return response()->json([
  //       'message' => 'Package element not found.',
  //     ], 404);
  //   }

  //   $pictures = Package_element_picture::where('package_element_id', $id)->get();

  //   if ($pictures->isEmpty()) {
  //     return response()->json([
  //       'message' => 'No pictures found for this package element.',
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
  //     'message' => 'Package element pictures retrieved successfully.',
  //     'images' => $imageData,
  //   ], 200);
  // }


  public function getPictureForElement($id)
  {
    $result = $this->tourismService->getPictureForElement($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => 'Failed to retrieve pictures.',
        'error' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (isset($result['not_found'])) {
      return response()->json([
        'message' => $result['not_found'],
        'images' => [],
      ], 404);
    }

    return response()->json([
      'message' => 'Package element pictures retrieved successfully.',
      'images' => $result,
    ], 200);
  }

  // public function addPictureElement(Request $request, $id)
  // {
  //   $element = Package_element::find($id);
  //   if (!$element) {
  //     return response()->json([
  //       'message' => 'Package element not found.',
  //     ], 404);
  //   }

  //   if (!$request->hasFile('picture')) {
  //     return response()->json([
  //       'message' => 'No picture file uploaded.',
  //     ], 400);
  //   }

  //   $file = $request->file('picture');

  //   if (!$file->isValid()) {
  //     return response()->json([
  //       'message' => 'Invalid picture file.',
  //     ], 400);
  //   }

  //   $path = $file->store('package_elements', 'public');

  //   $newPicture = new Package_element_picture();
  //   $newPicture->package_element_id = $id;
  //   $newPicture->picture_path = $path;
  //   $newPicture->save();

  //   return response()->json([
  //     'message' => 'Picture added successfully.',
  //     'picture' => [
  //       'id' => $newPicture->id,
  //       'path' => asset('storage/' . $path),
  //     ],
  //   ], 201);
  // }


  public function addPictureElement(TourismCompanyRequist $request, $id)
  {
    $data = $request->validated();
    $result = $this->tourismService->addPictureElement($data, $id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => 'Failed to add picture.',
        'error' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (isset($result['not_found'])) {
      return response()->json([
        'message' => $result['not_found'],
      ], 404);
    }

    if (isset($result['bad_request'])) {
      return response()->json([
        'message' => $result['bad_request'],
      ], 400);
    }

    return response()->json([
      'message' => 'Picture added successfully.',
      'picture' => $result,
    ], 201);
  }

  // public function deleteElementPicture($id)
  // {
  //   $picture = Package_element_picture::find($id);

  //   if (!$picture) {
  //     return response()->json([
  //       'message' => 'Picture not found.',
  //     ], 404);
  //   }

  //   try {
  //     if (Storage::disk('public')->exists($picture->picture_path)) {
  //       Storage::disk('public')->delete($picture->picture_path);
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



  public function deleteElementPicture($id)
  {
    $result = $this->tourismService->deleteElementPicture($id);

    if (isset($result['error'])) {
      return response()->json([
        'message' => 'An error occurred while deleting the picture.',
        'error' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (isset($result['not_found'])) {
      return response()->json([
        'message' => $result['not_found'],
      ], 404);
    }

    return response()->json([
      'message' => $result['message'],
    ], 200);
  }


  // public function getRecordsForPackage($id): JsonResponse
  // {
  //   try {
  //     $records = User_package::with('user')
  //       ->where('package_id', $id)
  //       ->get(['traveler_name', 'national_number', 'user_id']);

  //     if ($records->isEmpty()) {
  //       return response()->json([
  //         'status' => 'error',
  //         'message' => 'No users found for this package.'
  //       ], 404);
  //     }

  //     $formatted = $records->map(function ($record) {
  //       return [
  //         'traveler_name' => $record->traveler_name,
  //         'national_number' => $record->national_number,
  //         'user_name' => $record->user->name ?? 'غير معروف'
  //       ];
  //     });

  //     return response()->json([
  //       'status' => 'success',
  //       'data' => $formatted
  //     ]);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'status' => 'error',
  //       'message' => 'An unexpected error occurred.',
  //       'details' => $e->getMessage()
  //     ], 500);
  //   }
  // }



  public function getRecordsForPackage($id): JsonResponse
  {
    $result = $this->tourismService->getRecordsForPackage($id);

    if (isset($result['error'])) {
      return response()->json([
        'status' => 'error',
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (isset($result['not_found'])) {
      return response()->json([
        'status' => 'error',
        'message' => $result['not_found'],
      ], 404);
    }

    return response()->json([
      'status' => 'success',
      'data' => $result,
    ], 200);
  }


  // public function getMostPopularPackagesForCompany(): JsonResponse
  // {
  //   try {
  //     $user = Auth::user();

  //     $owner = DB::table('owners')
  //       ->where('user_id', $user->id)
  //       ->first();

  //     if (!$owner) {
  //       return response()->json([
  //         'status' => 'error',
  //         'message' => 'Owner not found for this user.'
  //       ], 404);
  //     }

  //     $company = DB::table('tourism_companies')
  //       ->where('owner_id', $owner->id)
  //       ->first();

  //     if (!$company) {
  //       return response()->json([
  //         'status' => 'error',
  //         'message' => 'Tourism company not found for this owner.'
  //       ], 404);
  //     }

  //     $packages = DB::table('packages')
  //       ->join('user_packages', 'packages.id', '=', 'user_packages.package_id')
  //       ->where('packages.tourism_company_id', $company->id)
  //       ->select(
  //         'packages.id',
  //         'packages.discription',
  //         'packages.total_price',
  //         'packages.payment_by_points',
  //         'packages.package_picture',
  //         DB::raw('COUNT(user_packages.user_id) as user_count')
  //       )
  //       ->groupBy(
  //         'packages.id',
  //         'packages.discription',
  //         'packages.total_price',
  //         'packages.payment_by_points',
  //         'packages.package_picture'
  //       )
  //       ->orderByDesc('user_count')
  //       ->get();

  //     if ($packages->isEmpty()) {
  //       return response()->json([
  //         'status' => 'error',
  //         'message' => 'No packages found for this tourism company.',
  //         'data' => []
  //       ], 404);
  //     }

  //     $formatted = $packages->map(function ($package) {
  //       return [
  //         'id' => $package->id,
  //         'discription' => $package->discription,
  //         'total_price' => $package->total_price,
  //         'payment_by_points' => $package->payment_by_points,
  //         'package_picture' => asset('storage/' . $package->package_picture),
  //         'user_count' => $package->user_count
  //       ];
  //     });

  //     return response()->json([
  //       'status' => 'success',
  //       'data' => $formatted
  //     ]);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'status' => 'error',
  //       'message' => 'An unexpected error occurred while retrieving popular packages.',
  //       'details' => $e->getMessage()
  //     ], 500);
  //   }
  // }



  public function getMostPopularPackagesForCompany(): JsonResponse
  {
    $result = $this->tourismService->getMostPopularPackagesForCompany();

    if (isset($result['error'])) {
      return response()->json([
        'status' => 'error',
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    if (isset($result['not_found'])) {
      return response()->json([
        'status' => 'error',
        'message' => $result['not_found'],
        'data' => [],
      ], 404);
    }

    return response()->json([
      'status' => 'success',
      'data' => $result,
    ], 200);
  }

  // public function getElementPackageById($id)
  // {
  //   $element = Package_element::find($id);

  //   if (!$element) {
  //     return response()->json([
  //       'message' => 'Package element not found.',
  //     ], 404);
  //   }

  //   $pictures = Package_element_picture::where('package_element_id', $id)->get();

  //   $imageData = $pictures->map(function ($pic) {
  //     return [
  //       'id' => $pic->id,
  //       'path' => asset('storage/' . $pic->picture_path),
  //     ];
  //   });

  //   $response = [
  //     'id' => $element->id,
  //     'name' => $element->name,
  //     'type' => $element->type,
  //     'discription' => $element->discription,
  //     'package_id' => $element->package_id,
  //     'images' => $imageData,
  //   ];

  //   return response()->json([
  //     'message' => 'Package element retrieved successfully.',
  //     'element' => $response,
  //   ], 200);
  // }

  public function getElementPackageById(TourismCompanyRequist $request, $id): JsonResponse
  {
    $data = $request->validated();
    $result = $this->tourismService->getElementPackageById($data, $id);

    if (isset($result['error'])) {
      return response()->json([
        'status' => 'error',
        'message' => $result['error'],
      ], $result['status'] ?? 404);
    }

    return response()->json([
      'status' => 'success',
      'message' => 'Package element retrieved successfully.',
      'data' => $result,
    ], 200);
  }
}
