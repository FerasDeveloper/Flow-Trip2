<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
  //


  protected $userservice;

  public function __construct(UserService $userservice)
  {
    $this->userservice = $userservice;
  }


  public function getRandomPackage()
  {
    $result = $this->userservice->getRandomPackages();

    if (isset($result['error'])) {
      return response()->json([
        'message' => $result['error'],
      ], $result['status'] ?? 500);
    }

    return response()->json([
      'message' => 'Random packages retrieved successfully.',
      'data' => $result,
    ], 200);
  }

  public function getRandomActivity()
  {
    $data = $this->userservice->getRandomActivity();
    return response()->json($data);
  }


  public function getActivity()
  {
    $data = $this->userservice->getActivity();

    return response()->json($data);
  }

  public function getRandomAccommodations()
  {
    $accommodations = $this->userservice->getRandomAccommodations();

    return response()->json($accommodations);
  }

  // public function filterFlights(Request $request)
  // {
  //   $flights = $this->userservice->filterFlights($request);

  //   return response()->json([
  //     'status' => true,
  //     'data'   => $flights
  //   ]);
  // }


  public function filterFlights(UserRequest $request)
  {
    $flights = $this->userservice->filterFlights($request);

    if (empty($flights)) {
      return response()->json([
        'status'  => false,
        'message' => 'no flights found',
        'data'    => []
      ]);
    }

    return response()->json([
      'status' => true,
      'data'   => $flights
    ]);
  }
  public function searchVehicles(UserRequest $request)
  {
    $filters = $request->only(['location', 'vehicle_name', 'car_type', 'people_count']);
    $vehicles = $this->userservice->searchVehicles($filters);

    if ($vehicles->isEmpty()) {
      return response()->json([
        'message' => 'no car found that s match',
        'data' => []
      ], 200);
    }

    return response()->json($vehicles);
  }
}
