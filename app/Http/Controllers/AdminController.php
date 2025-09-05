<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Models\Activity;
use App\Models\Owner_category;
use App\Models\Package;
use App\Models\User;
use App\Services\AdminService;
use App\Services\AuthRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

  private AdminService $adminService;
  private AuthRequestService $authRequestService;

  public function __construct(AdminService $adminService, authRequestService $authRequestService)
  {
    $this->adminService = $adminService;
    $this->authRequestService = $authRequestService;
  }

  public function get_all_requests()
  {
    return response()->json($this->authRequestService->get_all_requests());
  }

  public function show_request($id)
  {
    return response()->json($this->authRequestService->show_request($id));
  }

  public function edit_request(AdminRequest $request, $id)
  {
    return response()->json($this->authRequestService->edit_request($request->validated(), $id));
  }

  public function accept_request($id)
  {
    return response()->json($this->authRequestService->accept_request($id));
  }

  public function delete_request($id)
  {
    return response()->json($this->authRequestService->delete_request($id));
  }

  public function get_all_owners()
  {
    return response()->json($this->adminService->get_all_owners());
  }

  public function show_owner($id)
  {
    return response()->json($this->adminService->show_owner($id));
  }

  public function block($id)
  {
    return response()->json($this->adminService->toggleBlockStatus($id));
  }

  public function admin_search(AdminRequest $request)
  {
    $data = array_merge([
      'country'     => null,
      'name'        => null,
      'category_id' => null,
    ], $request->validated());
    return response()->json($this->adminService->admin_search($data));
  }

  public function getAllPackages()
  {
    // $user = Auth::user();
    // if (!in_array($user['role_id'], [1, 2])) {
    //   return response()->json(['message' => 'Authorization required']);
    // }
    $allpackages = Package::with('tourism_company')->get();

    return response()->json([
      "message" => 'success',
      "data" => $allpackages,
      "status" => 200
    ]);
  }

  // public function getPackage($id)
  // {
  //   $package = Package::with([
  //     'package_element.package_element_picture',
  //     'tourism_company'
  //   ])->find($id);

  //   if (!$package) {
  //     return response()->json([
  //       'error' => 'not found'
  //     ], 404);
  //   }

  //   if ($package->package_picture) {
  //     $package->package_picture = asset('storage/' . $package->package_picture);
  //   }

  //   if ($package->package_element) {
  //     foreach ($package->package_element as $element) {
  //       if ($element->package_element_picture) {
  //         foreach ($element->package_element_picture as $pic) {
  //           if ($pic->picture_path) {
  //             $pic->picture_path = asset('storage/' . $pic->picture_path);
  //           }
  //         }
  //       }
  //     }
  //   }

  //   return response()->json([
  //     'message' => 'success',
  //     'data' => $package,
  //     'status' => 200
  //   ]);
  // }



  public function getPackage($id)
  {
    $package = Package::with([
      'package_element.package_element_picture',
      'tourism_company.owner.user' // تحميل بيانات اليوزر
    ])->find($id);

    if (!$package) {
      return response()->json([
        'error' => 'not found'
      ], 404);
    }

    // تعديل رابط صورة الباقة
    if ($package->package_picture) {
      $package->package_picture = asset('storage/' . $package->package_picture);
    }

    // تعديل روابط صور عناصر الباقة
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

    return response()->json([
      'message' => 'success',
      'data' => $package,
      'status' => 200
    ]);
  }


  public function paybypoint($id)
  {
    $package = Package::find($id);

    if ($package) {
      // $package->payment_by_points = true;
      $package->payment_by_points = !$package->payment_by_points;


      $package->save();

      return response()->json(['message' => 'Updated successfully']);
    } else {
      return response()->json(['message' => 'Package not found'], 404);
    }
  }

  public function getAllUsers()
  {
    $users = User::query()->where('role_id', 3)->get();

    if ($users->isEmpty()) {
      return response()->json([
        "message" => "there is no user",
        "data" => [],
        "status" => 404
      ]);
    }

    return response()->json([
      "message" => "success",
      "data" => $users,
      "status" => 200
    ]);
  }

  public function filter_users(Request $request)
  {
    $users = User::query()->where('name', 'like', '%' . $request->name . '%')->where('role_id', 3)->get();
    return response()->json([
      'data' => $users
    ]);
  }

  public function filter_sub_admins(Request $request)
  {
    $users = User::query()->where('name', 'like', '%' . $request->name . '%')->where('role_id', 2)->get();
    return response()->json([
      'data' => $users
    ]);
  }

  public function createSubAdmin($id)
  {
    $user = User::find($id);

    if (!$user) {
      return response()->json([
        "message" => "user not found",
        "status" => 404
      ]);
    }

    $user->role_id = 2;
    $user->save();

    return response()->json([
      "message" => "Sub Admin create succes",
      "data" => $user,
      "status" => 200
    ]);
  }

  public function getAllSubAdmin()
  {
    $subAdmins = User::where('role_id', 2)->get();

    if ($subAdmins->isEmpty()) {
      return response()->json([
        "message" => "There are no Sub Admin users.",
        "data" => [],
        "status" => 404
      ]);
    }

    return response()->json([
      "message" => "Sub Admins data fetched successfully.",
      "data" => $subAdmins,
      "status" => 200
    ]);
  }

  public function removeSubAdmin($id)
  {
    $user = User::find($id);

    if (!$user) {
      return response()->json([
        "message" => "user not found",
        "status" => 404
      ]);
    }

    $user->role_id = 3;
    $user->save();

    return response()->json([
      "message" => "remove succes",
      "data" => $user,
      "status" => 200
    ]);
  }

  public function getAllActivity()
  {
    $activities = Activity::all();

    if ($activities->isEmpty()) {
      return response()->json([
        "message" => "there is no activity",
        "data" => [],
        "status" => 404
      ]);
    }

    return response()->json([
      "message" => "succes",
      "data" => $activities,
      "status" => 200
    ]);
  }

  public function addActivity(Request $request)
  {
    $request->validate([
      'name' => 'required'
    ]);

    if (Activity::where('name', $request->name)->exists()) {
      return response()->json([
        'message' => 'This activity name is already taken, please choose another.',
        'status' => 409
      ]);
    }

    $activity = new Activity();
    $activity->name = $request->name;
    $activity->save();

    return response()->json([
      'message' => 'Activity added successfully.',
      'data' => $activity,
      'status' => 201
    ]);
  }

  public function deleteactivity($id)
  {
    $activity = Activity::find($id);
    if (!$activity) {
      return response()->json([
        'message' =>
        'Activity not found',
        'status' =>
        404
      ]);
    }
    $activity->delete();
    return response()->json([
      'message' =>
      'Activity deleted successfully',
      'status' =>
      200
    ]);
  }


  public function addcatigory(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255|unique:owner_categories,name',
    ], [
      'name.required' => 'The category name is required.',
      'name.string' => 'The category name must be a string.',
      'name.max' => 'The category name may not be greater than 255 characters.',
      'name.unique' => 'This category name already exists.',
    ]);

    $category = new Owner_category();
    $category->name = $validated['name'];
    $category->save();

    return response()->json([
      'message' => 'Category added successfully.',
      'data' => $category,
    ], 201);
  }
}
