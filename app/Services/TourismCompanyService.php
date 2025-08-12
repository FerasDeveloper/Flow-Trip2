<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\Package;
use App\Models\Package_element;
use App\Models\Package_element_picture;
use App\Models\Tourism_company;
use App\Models\User_package;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourismCompanyService
{
  public function createPackage(Request $request)
  {
    $user = Auth::user();
    $owner = Owner::where('user_id', $user->id)->first();

    if (!$owner) {
      return [
        'error' => 'Owner not found for the current user.',
        'status' => 404,
      ];
    }

    $company = Tourism_company::where('owner_id', $owner->id)->first();

    if (!$company) {
      return [
        'error' => 'Tourism company not found for the current owner.',
        'status' => 404,
      ];
    }

    $imagePath = null;
    if ($request->hasFile('package_picture')) {
      $imagePath = $request->file('package_picture')->store('packages', 'public');
    }

    $package = Package::create([
      'tourism_company_id' => $company->id,
      'discription' => $request->discription,
      'total_price' => $request->total_price,
      'checked' => $request->checked,
      'payment_by_points' => 0,
      'package_picture' => $imagePath,
    ]);

    return [
      'data' => $package,
    ];
  }

  public function editPackage(array $data, $id)
  {
    $package = Package::find($id);

    if (!$package) {
      return [
        'error' => 'Package not found.',
        'status' => 404,
      ];
    }

    if (isset($data['discription'])) {
      $package->discription = $data['discription'];
    }

    if (isset($data['total_price'])) {
      $package->total_price = $data['total_price'];
    }

    if (isset($data['checked'])) {
      $package->checked = $data['checked'];
    }

    if (isset($data['package_picture'])) {
      $this->handleImageReplacement($package, $data['package_picture']);
    }

    $package->save();

    return [
      'data' => $package,
    ];
  }

  private function handleImageReplacement($package, $newImage)
  {
    if ($package->package_picture && Storage::disk('public')->exists($package->package_picture)) {
      Storage::disk('public')->delete($package->package_picture);
    }

    $newPath = $newImage->store('packages', 'public');
    $package->package_picture = $newPath;
  }

  public function deletePackage($id)
  {
    $package = Package::find($id);

    if (!$package) {
      return [
        'error' => 'Package not found.',
        'status' => 404,
      ];
    }

    try {
      if ($package->package_picture && Storage::disk('public')->exists($package->package_picture)) {
        Storage::disk('public')->delete($package->package_picture);
      }

      $package->delete();

      return [
        'data' => true,
      ];
    } catch (Exception $e) {
      return [
        'error' => 'Failed to delete the package.',
        'status' => 500,
      ];
    }
  }


  public function getPackagesfortourism()
  {
    $user = Auth::user();

    $owner = Owner::where('user_id', $user->id)->first();

    if (!$owner) {
      return [
        'error' => 'Owner not found for this user.',
        'status' => 404,
      ];
    }

    $company = Tourism_company::where('owner_id', $owner->id)->first();

    if (!$company) {
      return [
        'error' => 'Tourism company not found for this owner.',
        'status' => 404,
      ];
    }

    $packages = Package::where('tourism_company_id', $company->id)->get();

    return $packages->map(function ($package) {
      return [
        'id' => $package->id,
        'description' => $package->discription,
        'total_price' => $package->total_price,
        'checked' => $package->checked,
        'payment_by_points' => $package->payment_by_points,
        'picture' => $package->package_picture
          ? asset('storage/' . $package->package_picture)
          : null,
      ];
    });
  }
  public function addPackageElement(array $data, $packageId)
  {
    try {
      $element = Package_element::create([
        'name' => $data['name'],
        'type' => $data['type'],
        'discription' => $data['discription'],
        'package_id' => $packageId,
      ]);

      if (isset($data['pictures'])) {
        foreach ($data['pictures'] as $picture) {
          $path = $picture->store('package_elements', 'public');

          Package_element_picture::create([
            'package_element_id' => $element->id,
            'picture_path' => $path,
          ]);
        }
      }

      return $element;
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }

  public function editPackageElement(array $data, $id)
  {
    $element = Package_element::find($id);

    if (!$element) {
      return [
        'not_found' => 'Package element not found.',
        'status' => 404,
      ];
    }

    try {
      if (isset($data['name'])) {
        $element->name = $data['name'];
      }

      if (isset($data['type'])) {
        $element->type = $data['type'];
      }

      if (isset($data['discription'])) {
        $element->discription = $data['discription'];
      }

      $element->save();

      return $element;
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }

  public function deletePackageElement($id)
  {
    $element = Package_element::find($id);

    if (!$element) {
      return [
        'not_found' => 'Package element not found.',
        'status' => 404,
      ];
    }

    try {
      $pictures = $element->package_element_picture;

      foreach ($pictures as $pic) {
        if (Storage::disk('public')->exists($pic->picture_path)) {
          Storage::disk('public')->delete($pic->picture_path);
        }
        $pic->delete();
      }

      $element->delete();

      return [
        'message' => 'Package element and its pictures deleted successfully.',
      ];
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }

  public function getPictureForElement($id)
  {
    $element = Package_element::find($id);

    if (!$element) {
      return [
        'not_found' => 'Package element not found.',
        'status' => 404,
      ];
    }

    $pictures = Package_element_picture::where('package_element_id', $id)->get();

    if ($pictures->isEmpty()) {
      return [
        'not_found' => 'No pictures found for this package element.',
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

  public function addPictureElement(array $data, $id)
  {
    $element = Package_element::find($id);

    if (!$element) {
      return [
        'not_found' => 'Package element not found.',
        'status' => 404,
      ];
    }

    if (!isset($data['picture'])) {
      return [
        'bad_request' => 'No picture file provided.',
        'status' => 400,
      ];
    }

    try {
      $file = $data['picture'];
      $path = $file->store('package_elements', 'public');

      $newPicture = new Package_element_picture();
      $newPicture->package_element_id = $id;
      $newPicture->picture_path = $path;
      $newPicture->save();

      return [
        'id' => $newPicture->id,
        'path' => asset('storage/' . $path),
      ];
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }

  public function deleteElementPicture($id)
  {
    $picture = Package_element_picture::find($id);

    if (!$picture) {
      return [
        'not_found' => 'Picture not found.',
        'status' => 404,
      ];
    }

    try {
      if (Storage::disk('public')->exists($picture->picture_path)) {
        Storage::disk('public')->delete($picture->picture_path);
      }

      $picture->delete();

      return [
        'message' => 'Picture deleted successfully.',
      ];
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }


  public function getRecordsForPackage($id)
  {
    try {
      $records = DB::table('user_packages')
        ->join('users', 'user_packages.user_id', '=', 'users.id')
        ->where('user_packages.package_id', $id)
        ->select(
          'user_packages.traveler_name',
          'user_packages.national_number',
          'users.name as user_name',
          'users.email',
          'users.phone_number'
        )
        ->get();

      if ($records->isEmpty()) {
        return [
          'not_found' => 'لا يوجد حجوزات لهذا الباكيج.',
          'status' => 404,
        ];
      }

      return $records;
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }

  public function getMostPopularPackagesForCompany()
  {
    try {
      $user = Auth::user();

      $owner = DB::table('owners')->where('user_id', $user->id)->first();
      if (!$owner) {
        return [
          'error' => 'Owner not found for this user.',
          'status' => 404,
        ];
      }

      $company = DB::table('tourism_companies')->where('owner_id', $owner->id)->first();
      if (!$company) {
        return [
          'error' => 'Tourism company not found for this owner.',
          'status' => 404,
        ];
      }

      $packages = DB::table('packages')
        ->join('user_packages', 'packages.id', '=', 'user_packages.package_id')
        ->where('packages.tourism_company_id', $company->id)
        ->select(
          'packages.id',
          'packages.discription',
          'packages.total_price',
          'packages.payment_by_points',
          'packages.package_picture',
          DB::raw('COUNT(user_packages.user_id) as user_count')
        )
        ->groupBy(
          'packages.id',
          'packages.discription',
          'packages.total_price',
          'packages.payment_by_points',
          'packages.package_picture'
        )
        ->orderByDesc('user_count')
        ->get();

      if ($packages->isEmpty()) {
        return [
          'not_found' => 'No packages found for this tourism company.',
          'status' => 404,
        ];
      }

      return $packages->map(function ($package) {
        return [
          'id' => $package->id,
          'discription' => $package->discription,
          'total_price' => $package->total_price,
          'payment_by_points' => $package->payment_by_points,
          'package_picture' => $package->package_picture
            ? asset('storage/' . $package->package_picture)
            : null,
          'user_count' => $package->user_count,
        ];
      });
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }

  public function getElementPackageById(array $data, $id)
  {
    try {
      $element = Package_element::find($id);

      if (!$element) {
        return [
          'error' => 'Package element not found.',
          'status' => 404,
        ];
      }

      $pictures = Package_element_picture::where('package_element_id', $id)->get();

      $imageData = $pictures->map(function ($pic) {
        return [
          'id' => $pic->id,
          'path' => asset('storage/' . $pic->picture_path),
        ];
      });

      return [
        'id' => $element->id,
        'name' => $element->name,
        'type' => $element->type,
        'discription' => $element->discription,
        'package_id' => $element->package_id,
        'images' => $imageData,
      ];
    } catch (\Exception $e) {
      return [
        'error' => $e->getMessage(),
        'status' => 500,
      ];
    }
  }
}
