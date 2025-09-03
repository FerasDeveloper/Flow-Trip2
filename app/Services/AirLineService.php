<?php

namespace App\Services;

use App\Models\Air_line;
use App\Models\Flight;
use App\Models\Owner;
use App\Models\Plan_type;
use App\Models\Plane;
use App\Models\Rate;
use App\Models\Seat;
use App\Models\User;
use App\Models\User_flight;
use Illuminate\Support\Facades\Auth;

class AirLineService
{
    // ------- [planes] ------- //

    public function add_plane($request)
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }

        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }

        $air_line = Air_line::where('owner_id', $owner->id)->first();

        // validation handled by AirLineRequest

        $image = $request->file('plane_shape_diagram');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/plane_shape_diagram', $imageName);

        Plane::create([
            'airline_id' => $air_line->id,
            'plane_type_id' => $request['plane_type_id'],
            'seats_count' => $request['seats_count'],
            'plane_shape_diagram' => $imageName,
            'status' => $request['status'],
        ]);

        return ['message' => 'new plane added successfully'];
    }

    public function edit_plane($request, $id)
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }

        // validation handled by AirLineRequest

        if ($request->hasFile('plane_shape_diagram')) {
            $image = $request->file('plane_shape_diagram');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/plane_shape_diagram', $imageName);

            Plane::where('id', $id)->update([
                'plane_shape_diagram' => $imageName,
            ]);
        }

        Plane::where('id', $id)->update([
            'plane_type_id' => $request['plane_type_id'],
            'seats_count' => $request['seats_count'],
            'status' => $request['status'],
        ]);

        return ['message' => 'your plane updeted successfully'];
    }

    public function get_all_planes()
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }
        $air_line = Air_line::where('owner_id', $owner->id)->first();

        $planes = Plane::where('airline_id', $air_line->id)->get();
        $data = [];

        foreach ($planes as $plane) {
            $plane_type = Plan_type::where('id', $plane->plane_type_id)->first();

            $data[] = [
                'plane' => $plane,
                'plane_type' => $plane_type->name,
            ];
        }

        return ['planes' => $data];
    }

    public function get_single_plane($id)
    {
        $plane = Plane::where('id', $id)->first();
        $plane_type = Plan_type::where('id', $plane->plane_type_id)->first();

        return [
            'plane' => $plane,
            'plane_type' => $plane_type->name,
            'image_url' => asset('storage/plane_shape_diagram/' . $plane->plane_shape_diagram)
        ];
    }

    public function delete_plane($id)
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }

        Plane::where('id', $id)->delete();

        return ['message' => 'your plane deleted successfully'];
    }

    // ------- [flights] ------- //

    public function add_flight($request)
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }
        $air_line = Air_line::where('owner_id', $owner->id)->first();

        // validation handled by AirLineRequest

        $flight = Flight::create([
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

        $plane = Plane::where('id', $flight->plane_id)->first();
        $seats_count = $plane->seats_count;

        for ($i = 1; $i <= $seats_count; $i++) {
            Seat::create([
                'flight_id' => $flight->id,
                'seat_number' => $i,
                'price' => $flight->price,
            ]);
        }

        return ['message' => 'new flight added successfully', 'flight_id' => $flight->id];
    }

    public function edit_flight($request, $id)
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }
        $air_line = Air_line::where('owner_id', $owner->id)->first();

        // validation handled by AirLineRequest

        $flight = Flight::where('id', $id)->first();
        $offerPrice = $flight->offer_price;
        if ($request->has('offer_price')) {
            $offerPrice = $request['offer_price'];

            Seat::where('flight_id', $id)->update(['price' => $offerPrice]);
        }

        Flight::where('id', $id)->update([
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

        return ['message' => 'your flight updated successfully'];
    }

    public function edit_seats($request)
    {
        // validation handled by AirLineRequest
        $updated = Seat::whereIn('id', $request->seat_ids)
            ->update(['price' => $request->new_price]);

        return ['message' => 'selected seats has been updated successfully', 'updated_rows' => $updated];
    }

    public function get_all_flights()
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }
        $air_line = Air_line::where('owner_id', $owner->id)->first();
        $flights = Flight::where('air_line_id', $air_line->id)->latest()->get();

        return ['flights' => $flights];
    }

    public function get_flight_details($id)
    {
        $flight = Flight::where('id', $id)->first();
        $seats = Seat::where('flight_id', $id)->get();

        return ['flight' => $flight, 'seats' => $seats];
    }

    public function delete_flight($id)
    {
        Flight::where('id', $id)->delete();

        return ['message' => 'your flight deleted successfully'];
    }

    // ------- [evaluations] ------- //

    public function get_evaluation()
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        $ratings = Rate::where('owner_id', $owner->id)->get();

        if ($ratings->isEmpty()) {
            return ['message' => 'No ratings yet'];
        }

        $data = [];
        $sum = 0;
        $count = 0;

        foreach ($ratings as $rate) {
            $user = User::where('id', $rate->user_id)->first();

            $data[] = [
                'user' => $user,
                'rate' => $rate->rating,
            ];

            $sum += $rate->rating;
            $count++;
        }

        $average = $sum / $count;
        $floor_average = floor($average);

        return [
            'ratings' => $data,
            'average_rating' => $average,
            'floor_average' => $floor_average,
        ];
    }

    // ------- [reservations] ------- //

    public function get_flight_reservations($id)
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }

        $flight = Flight::where('id', $id)->first();
        $reservations = User_flight::where('flight_id', $flight->id)->latest()->get();

        return ['reservations' => $reservations];
    }

    public function get_all_reservations()
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }

        $air_line = Air_line::where('owner_id', $owner->id)->first();

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

        return ['reservations' => $data];
    }

    public function search_reservations_by_name($request)
    {
        $user = Auth::user();

        if ($user['role_id'] != 4) {
            return ['message' => 'Authorization required'];
        }
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner['owner_category_id'] != 2) {
            return ['message' => 'Authorization required'];
        }

        // validation handled by AirLineRequest
        $air_line = Air_line::where('owner_id', $owner->id)->first();

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

        return ['reservations' => $data];
    }
}
