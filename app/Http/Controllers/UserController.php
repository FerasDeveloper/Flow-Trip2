<?php

namespace App\Http\Controllers;

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
    $accommodations = $this->userservice->getRandomAccommodations(5);

    return response()->json($accommodations);
  }
}
