<?php

use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AirLineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralTaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TourismCompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiclyController;
use Illuminate\Support\Facades\Route;

Route::post('/CreateUser', [AuthController::class, 'user_Register']);
Route::get('/ReSendEmail/{email}', [AuthController::class, 'resend_email']);
Route::post('/Verification/{email}', [AuthController::class, 'verification']);
Route::post('/ReSetPassword/{email}', [AuthController::class, 'reset_password']);

Route::post('/CreateOwner/{email}', [AuthController::class, 'create_owner']);
Route::post('/Login', [AuthController::class, 'login']);

Route::get('/GetAllOwnerCategories', [GeneralTaskController::class, 'get_all_owners_categories']);
Route::get('/GetAllCountries', [GeneralTaskController::class, 'get_all_countries']);
Route::get('/GetAllAccommodationTypes', [GeneralTaskController::class, 'get_all_accommodation_types']);
Route::get('/GetAllCarTypes', [GeneralTaskController::class, 'get_all_car_types']);
Route::get('/GetAllPlaneTypes', [GeneralTaskController::class, 'get_all_plane_types']);
Route::get('/GetAllServices', [GeneralTaskController::class, 'get_all_services']);

// User
Route::get('/getRandomPackage', [UserController::class, 'getRandomPackage']);
Route::get('/getActivity', [UserController::class, 'getActivity']);
Route::get('/getRandomActivity', [UserController::class, 'getRandomActivity']);
Route::get('/getRandomAccommodations', [UserController::class, 'getRandomAccommodations']);
Route::post('/filterFlights', [UserController::class, 'filterFlights']);
Route::post('/searchVehicles', [UserController::class, 'searchVehicles']);
Route::post('/filterActivities', [UserController::class, 'filterActivities']);
Route::post('/ai/chat', [AiController::class, 'chat']);
Route::post('/ai/itinerary', [AiController::class, 'itinerary']);
Route::get('/getAllVehicles', [UserController::class, 'getAllVehicles']);
Route::get('/payments/balance', [UserController::class, 'getBalance']);
Route::get('/getRandomPackage', [UserController::class, 'getRandomPackage']);
Route::get('/getActivity', [UserController::class, 'getActivity']);
Route::get('/getRandomActivity', [UserController::class, 'getRandomActivity']);
Route::get('/getRandomAccommodations', [UserController::class, 'getRandomAccommodations']);
Route::post('/FilterAccommodation', [UserController::class, 'filter_accommodation']);

Route::get('/getAllActivity', [AdminController::class, 'getAllActivity']);
Route::get('/AccommodationDetails/{id}', [UserController::class, 'accommodation_details']);
Route::get('/RoomDetails/{id}', [UserController::class, 'room_details']);


Route::middleware('auth:sanctum')->group(function () {

  // Owner Id
  Route::get('/WhoAmI', [GeneralTaskController::class, 'who_am_i']);


  Route::get('/Logout', [AuthController::class, 'logout']);

  // Admin
  // Request
  Route::get('/GetAllRequests', [AdminController::class, 'get_all_requests']);
  Route::get('/ShowRequest/{id}', [AdminController::class, 'show_request']);
  Route::post('/EditRequest/{id}', [AdminController::class, 'edit_request']);
  Route::get('/AcceptRequest/{id}', [AdminController::class, 'accept_request']);
  Route::get('/DeleteRequest/{id}', [AdminController::class, 'delete_request']);

  Route::get('/getallpackage', [AdminController::class, 'getAllPackages']);
  Route::get('/getPackage/{id}', [AdminController::class, 'getPackage']);
  Route::post('/addActivity', [AdminController::class, 'addActivity']);
  Route::delete('/deleteactivity/{id}', [AdminController::class, 'deleteactivity']);
  Route::get('/paybypoint/{id}', [AdminController::class, 'paybypoint']);
  Route::post('/addcatigory', [AdminController::class, 'addcatigory']);

  // Owner
  Route::get('/GetAllOwners', [AdminController::class, 'get_all_owners']);
  Route::get('/ShowOwner/{id}', [AdminController::class, 'show_owner']);
  Route::get('/BlockOwner/{id}', [AdminController::class, 'block']);
  Route::post('/AdminSearch', [AdminController::class, 'admin_search']);

  // SubAdmin
  Route::get('/getalluser', [AdminController::class, 'getAllUsers']);
  Route::post('/filterusers', [AdminController::class, 'filter_users']);
  Route::get('/createSubAdmin/{id}', [AdminController::class, 'createSubAdmin']);
  Route::get('/getAllSubAdmin', [AdminController::class, 'getAllSubAdmin']);
  Route::post('/filterSubAdmins', [AdminController::class, 'filter_sub_admins']);
  Route::get('/removeSubAdmin/{id}', [AdminController::class, 'removeSubAdmin']);

  // AirLine
  //1. planes
  Route::post('/AddPlane', [AirLineController::class, 'add_plane']);
  Route::post('/EditPlane/{plane_id}', [AirLineController::class, 'edit_plane']);
  Route::get('/GetAllPlanes', [AirLineController::class, 'get_all_planes']);
  Route::get('/GetSinglePlane/{plane_id}', [AirLineController::class, 'get_single_plane']);
  Route::delete('/DeletePlane/{plane_id}', [AirLineController::class, 'delete_plane']);

  //2. flights
  Route::post('/AddFlight', [AirLineController::class, 'add_flight']);
  Route::post('/EditFlight/{flight_id}', [AirLineController::class, 'edit_flight']);
  Route::post('/EditSeats', [AirLineController::class, 'edit_seats']);
  Route::get('/GetFlightDetails/{flight_id}', [AirLineController::class, 'get_flight_details']);
  Route::get('/GetAllFlights', [AirLineController::class, 'get_all_flights']);
  Route::delete('/DeleteFlight/{flight_id}', [AirLineController::class, 'delete_flight']);

  //3. reservations
  Route::get('/GetFlightReservations/{flight_id}', [AirLineController::class, 'get_flight_reservations']);
  Route::get('/GetAllReservations', [AirLineController::class, 'get_all_reservations']);
  Route::post('/SearchReservationsByName', [AirLineController::class, 'search_reservations_by_name']);

  // General Tasks
  Route::get('/GetEvaluation', [AirLineController::class, 'get_evaluation']);
  Route::post('/AddPicture', [GeneralTaskController::class, 'add_picture']);
  Route::get('/DeletePicture/{picture_id}', [GeneralTaskController::class, 'delete_picture']);
  Route::post('/AddService', [GeneralTaskController::class, 'add_service']);
  Route::get('/DeleteService/{service_id}', [GeneralTaskController::class, 'delete_service']);
  Route::get('/ShowProfile', [GeneralTaskController::class, 'show_profile']);
  Route::post('/EditProfile', [GeneralTaskController::class, 'edit_profile']);

  // Accommodation
  Route::get('/ShowAccommodationRecords', [AccommodationController::class, 'show_records']);
  Route::get('/ShowOldAccommodationRecords', [AccommodationController::class, 'show_old_records']);
  Route::get('/ShowNewAccommodationRecords', [AccommodationController::class, 'show_new_records']);
  Route::get('/ShowPopularAccommodationRecords', [AccommodationController::class, 'show_popular_records']);
  Route::post('/FilterNameAccommodation', [AccommodationController::class, 'filter_name_accommodation']);
  Route::get('/ShowOffers', [AccommodationController::class, 'show_offers']);
  Route::get('/ShowAllRooms', [AccommodationController::class, 'show_all_rooms']);
  Route::get('/ShowRoomRecords/{id}', [AccommodationController::class, 'show_room_records']);
  Route::get('/ShowOldRoomRecords/{id}', [AccommodationController::class, 'show_old_room_records']);
  Route::get('/ShowRoom/{id}', [AccommodationController::class, 'show_room']);
  Route::post('/AddRoom', [AccommodationController::class, 'add_room']);
  Route::post('/EditRoom/{id}', [AccommodationController::class, 'edit_room']);
  Route::get('/DeleteRoom/{id}', [AccommodationController::class, 'delete_room']);

  // User
  Route::post('/BookRoom/{id}', [UserController::class, 'book_room']);
  Route::post('/BookAccommodation/{id}', [UserController::class, 'book_accommodation']);

  // Notifications
  Route::get('/GetAllNotifications', [NotificationController::class, 'get_all_notifications']);


  Route::post('/book_package', [UserController::class, 'book_package']);
  Route::post('/book_flight', [UserController::class, 'book_flight']);


  // tourism company
  Route::prefix('tourism')->group(function () {
    Route::post('/createPackage', [TourismCompanyController::class, 'createPackage']);
    Route::post('/editPackage/{id}', [TourismCompanyController::class, 'editPackage']);
    Route::delete('/deletePackage/{id}', [TourismCompanyController::class, 'deletePackage']);
    Route::get('/getPackagesfortourism', [TourismCompanyController::class, 'getPackagesfortourism']);
    Route::post('/addPackageElement/{id}', [TourismCompanyController::class, 'addPackageElement']);
    Route::post('/editPackageElement/{id}', [TourismCompanyController::class, 'editPackageElement']);
    Route::delete('/deletePackageElement/{id}', [TourismCompanyController::class, 'deletePackageElement']);
    Route::get('/getPictureForElement/{id}', [TourismCompanyController::class, 'getPictureForElement']);
    Route::post('/addPictureElement/{id}', [TourismCompanyController::class, 'addPictureElement']);
    Route::delete('/deleteElementPicture/{id}', [TourismCompanyController::class, 'deleteElementPicture']);
    Route::get('/getrecordsforpackage/{id}', [TourismCompanyController::class, 'getrecordsforpackage']);
    Route::get('/getMostPopularPackagesForCompany', [TourismCompanyController::class, 'getMostPopularPackagesForCompany']);
    Route::get('/getElementPackageById/{id}', [TourismCompanyController::class, 'getElementPackageById']);
  });


  // Vehicly Owner
  Route::prefix('vehicleowner')->group(function () {
    Route::post('/createVehicle', [VehiclyController::class, 'createVehicly']);
    Route::post('/editVehicle/{id}', [VehiclyController::class, 'editVehicly']);
    Route::delete('/deleteVehicly/{id}', [VehiclyController::class, 'deleteVehicly']);
    Route::post('/createPictureCar', [VehiclyController::class, 'createPictureCar']);
    Route::get('/getAllPicture/{id}', [VehiclyController::class, 'getAllPicture']);
    Route::delete('/deletePictureCar/{id}', [VehiclyController::class, 'deletePictureCar']);
    Route::get('/getAllViclyForuser/{id}', [VehiclyController::class, 'getAllViclyForuser']);
    Route::get('/getVehicleById/{id}', [VehiclyController::class, 'getVehicleById']);
  });
});
