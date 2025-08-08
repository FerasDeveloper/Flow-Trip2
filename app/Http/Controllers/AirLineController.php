<?php

namespace App\Http\Controllers;

use App\Models\Air_line;
use App\Models\Flight;
use App\Models\Owner;
use App\Models\Plan_type;
use App\Models\Plane;
use App\Models\Rate;
use App\Models\Seat;
use App\Models\User;
use App\Models\User_flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AirLineController extends Controller
{
  //-------[planes]-------// 

  public function add_plane(Request $request)
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $air_line = Air_line::query()->where('owner_id', $owner->id)->first();

    $request->validate([
      'plane_type_id' => 'required',
      'seats_count' => 'required',
      'plane_shape_diagram' => 'required|image|mimes:jpg,jpeg,png|max:2048',
      'status' => 'required',
    ]);
    $image = $request->file('plane_shape_diagram');
    $imageName = time() . '_' . $image->getClientOriginalName();
    $image->storeAs('public/plane_shape_diagram', $imageName);

    $plane = Plane::query()->create([
      'airline_id' => $air_line->id,
      'plane_type_id' => $request['plane_type_id'],
      'seats_count' => $request['seats_count'],
      'plane_shape_diagram' => $imageName,
      'status' => $request['status'],
    ]);

    return response()->json([
      'message' => 'new plane added successfully',
    ]);
  }


  public function edit_plane(Request $request, $id)
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }

    $request->validate([
      'plane_type_id' => 'required',
      'seats_count' => 'required',
      'plane_shape_diagram' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      'status' => 'required',
    ]);

    if ($request->hasFile('plane_shape_diagram')) {
        $image = $request->file('plane_shape_diagram');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/plane_shape_diagram', $imageName);
        
        $plane = Plane::query()->where('id', $id)->update([
          'plane_shape_diagram' => $imageName,
        ]);
    }

    $plane = Plane::query()->where('id', $id)->update([
      'plane_type_id' => $request['plane_type_id'],
      'seats_count' => $request['seats_count'],
      'status' => $request['status'],
    ]);

    return response()->json([
      'message' => 'your plane updeted successfully',
    ]);
  }


  public function get_all_planes()
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $air_line = Air_line::query()->where('owner_id', $owner->id)->first();

    $planes = Plane::query()->where('airline_id', $air_line->id)->get();
    $data = [];

    foreach ($planes as $plane) {
      $plane_type = Plan_type::query()->where('id', $plane->plane_type_id)->first();

      $data[] = [
        'plane' => $plane,
        'plane_type' => $plane_type->name,
      ];
    }

    return response()->json([
      'planes' => $data,
    ]);
  }


  public function get_single_plane($id)
  {
    $user = Auth::user();
    
    $plane = Plane::query()->where('id', $id)->first();
    $plane_type = Plan_type::query()->where('id', $plane->plane_type_id)->first();
    

    return response()->json([
       'plane' => $plane,
       'plane_type' => $plane_type->name,
       'image_url' => asset('storage/plane_shape_diagram/' . $plane->plane_shape_diagram)
    ]);
  }


  public function delete_plane($id)
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }

    $plane = Plane::query()->where('id', $id)->delete();

    return response()->json([
      'message' => 'your plane deleted successfully',
    ]);
  }


  //-------[flights]-------//
  
  public function add_flight(Request $request)
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $air_line = Air_line::query()->where('owner_id', $owner->id)->first();

    $request->validate([
      'plane_id' => 'required',
      'price' => 'required',
      'flight_number' => 'required',
      'starting_point_location' => 'required',
      'landing_point_location' => 'required',
      'starting_airport' => 'required',
      'landing_airport' => 'required',
      'start_time' => 'required',
      'land_time' => 'required',
      'estimated_time' => 'required',
      'date' => 'required|date',
    ]);


    $flight = Flight::query()->create([
      'air_line_id' => $air_line->id,
      'plane_id' => $request['plane_id'],
      'price' => $request['price'],
      'offer_price' => 0,
      'flight_number' => $request['flight_number'],
      'starting_point_location' => $request['starting_point_location'],
      'landing_point_location' => $request['landing_point_location'],
      'starting_airport' => $request['starting_airport'],
      'landing_airport' => $request['landing_airport'],
      'start_time' => $request['start_time'],
      'land_time' => $request['land_time'],
      'estimated_time' => $request['estimated_time'],
      'date' => $request['date'],
    ]);

    $plane = Plane::query()->where('id', $flight->plane_id)->first();
    $seats_count = $plane->seats_count;

    for($i = 1; $i <= $seats_count; $i++){
      $seat = Seat::query()->create([
        'flight_id' => $flight->id,
        'seat_number' => $i,
        'price' => $flight->price,
      ]);
    }

    return response()->json([
      'message' => 'new flight added successfully',
      'flight_id' => $flight->id,
    ]);
  }


  public function edit_flight(Request $request, $id)
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $air_line = Air_line::query()->where('owner_id', $owner->id)->first();

    $request->validate([
      'plane_id' => 'required',
      'price' => 'required',
      'flight_number' => 'required',
      'starting_point_location' => 'required',
      'landing_point_location' => 'required',
      'starting_airport' => 'required',
      'landing_airport' => 'required',
      'start_time' => 'required',
      'land_time' => 'required',
      'estimated_time' => 'required',
      'date' => 'required|date',
    ]);

    $flight = Flight::query()->where('id', $id)->first();
    $offerPrice = $flight->offer_price;
    if ($request->has('offer_price')) {
      $offerPrice = $request['offer_price'];
    }


    $flight_update = Flight::query()->where('id', $id)->update([
      'air_line_id' => $air_line->id,
      'plane_id' => $request['plane_id'],
      'price' => $request['price'],
      'offer_price' => $offerPrice,
      'flight_number' => $request['flight_number'],
      'starting_point_location' => $request['starting_point_location'],
      'landing_point_location' => $request['landing_point_location'],
      'starting_airport' => $request['starting_airport'],
      'landing_airport' => $request['landing_airport'],
      'start_time' => $request['start_time'],
      'land_time' => $request['land_time'],
      'estimated_time' => $request['estimated_time'],
      'date' => $request['date'],
    ]);

    return response()->json([
      'message' => 'your flight updated successfully',
    ]);
  }


  public function edit_seats(Request $request){
    $request->validate([
        'seat_ids' => 'required|array',
        'new_price' => 'required|numeric|min:0'
    ]);

    $updated = Seat::whereIn('id', $request->seat_ids)
                ->update(['price' => $request->new_price]);

    return response()->json([
        'message' => 'selected seats has been updated successfully',
        'updated_rows' => $updated
    ]);
  }


  public function get_all_flights()
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $air_line = Air_line::query()->where('owner_id', $owner->id)->first();
    $flights = Flight::query()->where('air_line_id', $air_line->id)->latest()->get();

    return response()->json([
       'flights' => $flights,
    ]);
  }


  public function get_flight_details($id)
  {
    $user = Auth::user();
    
    $flight = Flight::query()->where('id', $id)->first();
    //$plane = Plane::query()->where('id', $flight->plane_id)->first();
    $seats = Seat::query()->where('flight_id', $id)->get();
    

    return response()->json([
       'flight' => $flight,
       'seats' => $seats
    ]);
  }


  public function delete_flight($id)
  {
    $user = Auth::user();
    
    $flight = Flight::query()->where('id', $id)->delete();    

    return response()->json([
       'message' => 'your flight deleted successfully',
    ]);
  }


  //-------[evaluations]-------//

  public function get_evaluation()
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    $ratings = Rate::where('owner_id', $owner->id)->get();

    if ($ratings->isEmpty()) {
        return response()->json([
            'message' => 'No ratings yet'
        ]);
    }

    $data = [];
    $sum = 0;
    $count = 0;

    foreach ($ratings as $rate) {
        $user = User::query()->where('id', $rate->user_id)->first(); 

          $data[] = [
              'user' => $user,
              'rate' => $rate->rating,
          ];
        
        $sum += $rate->rating;
        $count++;
    }

    $average = $sum / $count;
    $floor_average = floor($average);

    return response()->json([
      'ratings' => $data,
      'average_rating' => $average,
      'floor_average' => $floor_average,
    ]);
  }

  //-------[reservations]-------//

  public function get_flight_reservations($id)
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }

    $flight = Flight::query()->where('id', $id)->first();
    $reservations = User_flight::query()->where('flight_id', $flight->id)->latest()->get();

    return response()->json([
      'reservations' => $reservations
    ]);
  }


  public function get_all_reservations()
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }

    $air_line = Air_line::query()->where('owner_id', $owner->id)->first();
    
    $flight_ids = Flight::where('air_line_id', $air_line->id)->pluck('id');

    $reservations = User_flight::whereIn('flight_id', $flight_ids)->latest()->get();

    $data = [];

    foreach ($reservations as $reservation) {
        $flight = Flight::find($reservation->flight_id);

        $data[] = [
            'reservation' => $reservation,
            'flight_details' => $flight,
        ];
    }

    return response()->json([
      'reservations' => $data
    ]);
  }


  public function search_reservations_by_name(Request $request)
  {
    $user = Auth::user();
    
    if ($user['role_id'] != 4) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }
    $owner = Owner::query()->where('user_id', $user->id)->first();
    if ($owner['owner_category_id'] != 2) {
        return response()->json([
          'message' => 'Authorization required'
        ]);
    }

    $request->validate([
        'name' => 'required',
    ]);

    $air_line = Air_line::query()->where('owner_id', $owner->id)->first();
    
    $flight_ids = Flight::where('air_line_id', $air_line->id)->pluck('id');

    $reservations = User_flight::whereIn('flight_id', $flight_ids)
           ->where('traveler_name', $request['name'])->latest()->get();

    $data = [];

    foreach ($reservations as $reservation) {
        $flight = Flight::find($reservation->flight_id);

        $data[] = [
            'reservation' => $reservation,
            'flight_details' => $flight,
        ];
    }

    return response()->json([
      'reservations' => $data
    ]);
  }

}
