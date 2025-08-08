<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccommodationRequest;
use App\Services\AccommodationService;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
  private AccommodationService $service;

  public function __construct(AccommodationService $service)
  {
    $this->service = $service;
  }


  public function filter_name_accommodation(AccommodationRequest $request)
  {
    $data = $request->validated();
    return $this->service->filter_name_accommodation($data);
  }

  public function show_offers()
  {
    return $this->service->show_offers();
  }

  public function show_all_rooms()
  {
    return $this->service->show_all_rooms();
  }


  public function add_room(AccommodationRequest $request)
  {
    $data = $request->validated();
    $images = $request->file('images', []);
    return $this->service->add_room($data, $images);
  }

  public function show_room($id)
  {
    return $this->service->show_room($id);
  }

  public function edit_room(AccommodationRequest $request, $id)
  {
    $data = $request->validated();
    $remainingPictureIds = [];
    $deletedPictureIds = [];
    $images = [];

    $offerPrice = $request->offer_price ?? null;
    if ($request->has('remaining_picture_ids')) {
      $remainingPictureIds = $request->input('remaining_picture_ids');
    }
    if ($request->has('deleted_picture_ids')) {
      $deletedPictureIds = $request->input('deleted_picture_ids');
    }
    if ($request->hasFile('images')) {
      $images = $request->file('images');
    }
    return $this->service->edit_room(
      $data,
      $remainingPictureIds,
      $deletedPictureIds,
      $images,
      $offerPrice,
      $id
    );
  }

  public function delete_room($id)
  {
    return $this->service->delete_room($id);
  }

  public function show_records()
  {
    return $this->service->show_records();
  }

  public function show_room_records($id)
  {
    return $this->service->show_room_records($id);
  }

  public function show_old_room_records($id)
  {
    return $this->service->show_old_room_records($id);
  }

  public function show_old_records()
  {
    return $this->service->show_old_records();
  }

  public function show_new_records()
  {
    return $this->service->show_new_records();
  }

  public function show_popular_records()
  {
    return $this->service->show_popular_records();
  }
}
