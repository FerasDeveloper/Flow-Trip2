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



  public function edit_profile(GeneralTaskFormRequest $request){
  $data = $request->validated();
    $remainingPictureIds = [];
    $deletedPictureIds = [];
    $images = [];
    
    if ($request->has('remaining_picture_ids')) {
      $remainingPictureIds = $request->input('remaining_picture_ids');
    }
    if ($request->has('deleted_picture_ids')) {
      $deletedPictureIds = $request->input('deleted_picture_ids');
    }
    if ($request->hasFile('images')) {
      $images = $request->file('images');
    }
    return response()->json(
      $this->service->edit_profile(
      $data,
      $remainingPictureIds,
      $deletedPictureIds,
      $images,
    ));
  }
}
