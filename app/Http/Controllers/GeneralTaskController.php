<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneralTaskFormRequest;

use App\Models\Owner;
use App\Models\Picture;
use App\Models\User;
use App\Services\GeneralTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GeneralTaskController extends Controller
{

  protected GeneralTaskService $service;

    public function __construct(GeneralTaskService $service)
    {
        $this->service = $service;
    }

    public function who_am_i()
    {
        $data = $this->service->who_am_i();
        return response()->json([
            'data' => $data
        ]);
    }

    public function get_all_owners_categories()
    {
        $owners_category = $this->service->get_all_owners_categories();
        return response()->json([
            'owners_categories' => $owners_category
        ]);
    }

    public function get_all_countries()
    {
        $country = $this->service->get_all_countries();
        return response()->json([
            'countries' => $country
        ]);
    }

    public function get_all_accommodation_types()
    {
        $accommodation_type = $this->service->get_all_accommodation_types();
        return response()->json([
            'accommodation_types' => $accommodation_type
        ]);
    }

    public function get_all_car_types()
    {
        $car_type = $this->service->get_all_car_types();
        return response()->json([
            'car_types' => $car_type
        ]);
    }

    public function get_all_plane_types()
    {
        $plane_type = $this->service->get_all_plane_types();
        return response()->json([
            'plane_types' => $plane_type
        ]);
    }

    public function get_all_services()
    {
        $service = $this->service->get_all_services();
        return response()->json([
            'services' => $service
        ]);
    }

    public function show_profile()
    {
        $data = $this->service->show_profile();
        return response()->json($data);
    }

    public function add_picture(GeneralTaskFormRequest $request)
    {
        $result = $this->service->add_picture($request);
        return response()->json($result);
    }

    public function delete_picture($id)
    {
        $result = $this->service->delete_picture($id);
        return response()->json($result);
    }

    public function add_service(GeneralTaskFormRequest $request)
    {
        $result = $this->service->add_service($request);
        return response()->json($result);
    }

    public function delete_service($id)
    {
        $result = $this->service->delete_service($id);
        return response()->json($result);
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
