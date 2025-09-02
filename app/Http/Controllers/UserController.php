<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Services\PaymentService;
use App\Services\UserService;
use Exception;
use GuzzleHttp\Psr7\Request;

class UserController extends Controller
{
  //

  protected $userservice;
  protected $paymentService;

  public function __construct(UserService $userservice, PaymentService $paymentService)
  {
    $this->userservice = $userservice;
    $this->paymentService = $paymentService;
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

  public function filter_accommodation(UserRequest $request)
  {
    return response()->json($this->userservice->filter_accommodation($request->validated()));
  }

  public function accommodation_details($id)
  {
    return response()->json($this->userservice->accommodation_details($id));
  }

  public function room_details($id)
  {
    return response()->json($this->userservice->room_details($id));
  }

  public function book_room(UserRequest $request, $id)
  {
    try {
      $validatedData = $request->validated();
      $availabilityCheck = $this->userservice->check_room_availability($id, $validatedData['start_date'], $validatedData['end_date']);

      if (!$availabilityCheck['available']) {
        return response()->json([
          'success' => false,
          'message' => $availabilityCheck['message']
        ]);
      }

      $payment = $this->paymentService->processPayment($validatedData);
      if ($payment['success'] == true) {
        unset($validatedData['stripeToken'], $validatedData['amount']);

        $bookingResult = $this->userservice->book_room($validatedData, $id);

        return response()->json([
          'success' => true,
          'payment' => $payment,
          'booking' => $bookingResult
        ]);
      } else {
        return response()->json('Something went wrong');
      }
    } catch (Exception $e) {
      return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

  public function book_accommodation(UserRequest $request, $id)
  {
    try {
      $validatedData = $request->validated();
      $availabilityCheck = $this->userservice->check_accommodation_availability($id, $validatedData['start_date'], $validatedData['end_date']);

      if (!$availabilityCheck['available']) {
        return response()->json([
          'success' => false,
          'message' => $availabilityCheck['message']
        ]);
      }

      $payment = $this->paymentService->processPayment($validatedData);

      if ($payment['success'] == true) {
        unset($validatedData['stripeToken'], $validatedData['amount']);

        $bookingResult = $this->userservice->book_accommodation($validatedData, $id);

        return response()->json([
          'success' => true,
          'payment' => $payment,
          'booking' => $bookingResult
        ]);
      } else {
        return response()->json('Something went wrong');
      }
    } catch (Exception $e) {
      return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

  public function getBalance()
  {
    return response()->json($this->paymentService->getBalance());
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

  // public function filterActivities(UserRequest $request)
  // {
  //   $filters = $request->only(['activity_name', 'country_name', 'location']);
  //   $result = $this->userservice->filterActivities($filters);

  //   return response()->json([
  //     'success' => true,
  //     'data'    => $result
  //   ]);
  // }
  public function filterActivities(UserRequest $request)
  {
    $filters = $request->only(['activity_name', 'country_name', 'location']);
    $result = $this->userservice->filterActivities($filters);

    if (isset($result['message'])) {
      return response()->json([
        'success' => false,
        'message' => $result['message'],
        'data'    => []
      ]);
    }

    return response()->json([
      'success' => true,
      'data'    => $result
    ]);
  }

  public function getAllVehicles()
  {
    $vehicles = $this->userservice->getAllVehicles();
    return response()->json([
      'status' => 'success',
      'data' => $vehicles
    ], 200);
  }

  public function book_package(UserRequest $request)
  {
    $result = $this->userservice->bookPackage($request->validated());

    return response()->json($result, $result['success'] ? 200 : 400);
  }
  public function book_flight(UserRequest $request)
  {
    $result = $this->userservice->bookFlight($request->validated());

    return response()->json($result, $result['success'] ? 200 : 400);
  }
}
