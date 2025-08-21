<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Services\PaymentService;
use App\Services\UserService;
use Exception;

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
    $accommodations = $this->userservice->getRandomAccommodations(5);

    return response()->json($accommodations);
  }

  public function filter_accommodation(UserRequest $request)
  {
    return response()->json($this->userservice->filter_accommodation($request->validated()));
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
}
