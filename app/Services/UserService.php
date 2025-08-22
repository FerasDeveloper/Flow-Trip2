<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Accommodation_type;
use App\Models\Activity;
use App\Models\Activity_owner;
use App\Models\Flight;
use App\Models\Owner;
use App\Models\Package;
use App\Models\Package_element;
use App\Models\Package_element_picture;
use App\Models\Room;
use App\Models\Picture;
use App\Models\Tourism_company;
use App\Models\User;
use App\Models\User_accommodation;
use App\Models\User_package;
use App\Models\User_room;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserService
{
  public function getRandomPackages()
  {
    $packagesQuery = Package::query();

    if ($packagesQuery->count() <= 5) {
      $packages = $packagesQuery->get();
    } else {
      $packages = $packagesQuery->inRandomOrder()->limit(5)->get();
    }

    return $packages->map(function ($package) {
      return [
        'id' => $package->id,
        'description' => $package->discription,
        'total_price' => $package->total_price,
        'checked' => $package->checked,
        'payment_by_points' => $package->payment_by_points,
        'picture' => $package->package_picture
          ? asset('storage/' . $package->package_picture)
          : null,
      ];
    });
  }

  // public function getActivity()
  // {
  //   return DB::table('activity_owners')
  //     ->join('activities', 'activity_owners.activity_id', '=', 'activities.id')
  //     ->join('owners', 'activity_owners.owner_id', '=', 'owners.id')
  //     ->join('users', 'owners.user_id', '=', 'users.id')
  //     ->join('countries', 'owners.country_id', '=', 'countries.id')
  //     ->select(
  //       'activities.id as id',
  //       'activities.name as activity_name',
  //       'activity_owners.owner_name',
  //       'owners.description',
  //       'owners.location',
  //       'users.phone_number',
  //       'countries.name as country_name'
  //     )
  //     ->get();
  // }
  public function getActivity()
  {
    $records = DB::table('activity_owners')
      ->join('activities', 'activity_owners.activity_id', '=', 'activities.id')
      ->join('owners', 'activity_owners.owner_id', '=', 'owners.id')
      ->join('users', 'owners.user_id', '=', 'users.id')
      ->join('countries', 'owners.country_id', '=', 'countries.id')
      ->select(
        'activities.id as id',
        'activities.name as activity_name',
        'activity_owners.owner_name',
        'owners.id as owner_id',
        'owners.description',
        'owners.location',
        'users.phone_number',
        'countries.name as country_name'
      )
      ->get();

    return $records->map(function ($record) {
      $picture = Picture::where('owner_id', $record->owner_id)
        ->orderBy('id', 'asc')
        ->value('reference');

      return [
        'id'            => $record->id,
        'activity_name' => $record->activity_name,
        'owner_name'    => $record->owner_name,
        'description'   => $record->description,
        'location'      => $record->location,
        'phone_number'  => $record->phone_number,
        'country_name'  => $record->country_name,
        'picture'       => $picture ? url($picture) : null,
      ];
    });
  }


  // public function getRandomActivity()
  // {
  //   $activityOwnersQuery = Activity_owner::query();
  //   if ($activityOwnersQuery->count() <= 5) {
  //     $records = $activityOwnersQuery->get();
  //   } else {
  //     $records = $activityOwnersQuery->inRandomOrder()->limit(5)->get();
  //   }
  //   return $records->map(function ($record) {
  //     $activity = Activity::find($record->activity_id);
  //     $ownerData = Owner::join('users', 'owners.user_id', '=', 'users.id')
  //       ->join('countries', 'owners.country_id', '=', 'countries.id')
  //       ->where('owners.id', $record->owner_id)
  //       ->select(
  //         'owners.description',
  //         'owners.location',
  //         'users.phone_number',
  //         'countries.name as country_name'
  //       )
  //       ->first();
  //     return [
  //       'id'            => $activity->id ?? null,
  //       'activity_name' => $activity->name ?? null,
  //       'owner_name'    => $record->owner_name,
  //       'description'   => $ownerData->description ?? null,
  //       'location'      => $ownerData->location ?? null,
  //       'phone_number'  => $ownerData->phone_number ?? null,
  //       'country_name'  => $ownerData->country_name ?? null,
  //     ];
  //   });
  // }

  public function getRandomActivity()
  {
    $activityOwnersQuery = Activity_owner::query();

    if ($activityOwnersQuery->count() <= 5) {
      $records = $activityOwnersQuery->get();
    } else {
      $records = $activityOwnersQuery->inRandomOrder()->limit(5)->get();
    }

    return $records->map(function ($record) {
      $activity = Activity::find($record->activity_id);

      $ownerData = Owner::join('users', 'owners.user_id', '=', 'users.id')
        ->join('countries', 'owners.country_id', '=', 'countries.id')
        ->where('owners.id', $record->owner_id)
        ->select(
          'owners.id',
          'owners.description',
          'owners.location',
          'users.phone_number',
          'countries.name as country_name'
        )
        ->first();

      // get first picture for this owner
      $picture = Picture::where('owner_id', $record->owner_id)
        ->orderBy('id', 'asc')
        ->value('reference');

      return [
        'id' => $activity->id ?? null,
        'activity_name' => $activity->name ?? null,
        'owner_name' => $record->owner_name,
        'description' => $ownerData->description ?? null,
        'location' => $ownerData->location ?? null,
        'phone_number' => $ownerData->phone_number ?? null,
        'country_name' => $ownerData->country_name ?? null,
        'owner_name'    => $record->owner_name,
        'description'   => $ownerData->description ?? null,
        'location'      => $ownerData->location ?? null,
        'phone_number'  => $ownerData->phone_number ?? null,
        'country_name'  => $ownerData->country_name ?? null,
        'picture'       => $picture ? url($picture) : null,
      ];
    });
  }

  public function getRandomAccommodations()
  {
    return DB::table('accommodations')
      ->join('accommodation_types', 'accommodations.accommodation_type_id', '=', 'accommodation_types.id')
      ->join('owners', 'accommodations.owner_id', '=', 'owners.id')
      ->select(
        'accommodations.*',
        'accommodation_types.name as type_name',
        'owners.description as owner_description',
        'owners.location as owner_location',
        DB::raw('(SELECT reference FROM pictures WHERE owner_id = owners.id LIMIT 1) as owner_picture')
      )
      ->inRandomOrder()
      ->limit(5)
      ->get();
  }

  public function filter_accommodation($request)
  {
    $user = Auth::user();
    $user0 = User::query()->where('id', $user->id)->first();
    if ($user0->role_id != 3) {
      return ['message' => 'You are not allowed to perform this action'];
    }

    $type_id = $request['type_id'];
    $name = $request['name'];
    $destination = $request['destination'];
    $country_id = $request['country_id'];
    $start_date = $request['start_date'];
    $end_date = $request['end_date'];
    $people = $request['people_count'];

    if ($type_id == '') {
      $accommodationQuery = Accommodation::query();
      $accommodations = $accommodationQuery
        ->join('owners', 'accommodations.owner_id', '=', 'owners.id')
        ->where(function ($query) use ($name, $destination, $country_id) {
          if ($name != '') {
            $query->where('accommodations.accommodation_name', 'LIKE', '%' . $name . '%');
          }
          if ($destination != '') {
            $query->where('owners.location', 'LIKE', '%' . $destination . '%');
          }
          if ($country_id != '') {
            $query->where('owners.country_id', $country_id);
          }
        })
        ->select('accommodations.*')
        ->get();

      $hotels = collect([]);
      $others = collect([]);
      foreach ($accommodations as $accommodation) {
        if ($accommodation->accommodation_type_id == 1) {
          $hotels->push($accommodation);
        } else {
          $others->push($accommodation);
        }
      }

      $accommodationWithAvailableRooms = $hotels->map(function ($hotel) use ($start_date, $end_date, $people) {
        $rooms = Room::query()->where('accommodation_id', $hotel->id)->get();

        $availableRooms = $rooms->filter(function ($room) use ($start_date, $end_date, $people) {
          if ($room->people_count < $people) {
            return false;
          }

          $overlappingBookings = User_room::query()
            ->where('room_id', $room->id)
            ->where(function ($query) use ($start_date, $end_date) {
              $query
                ->where('start_date', '<=', $end_date)
                ->where('end_date', '>=', $start_date);
            })
            ->exists();
          return !$overlappingBookings;
        });

        $hotel['rooms'] = $availableRooms->values();
        return $hotel;
      });

      $accommodationsWithRooms = $accommodationWithAvailableRooms->filter(function ($accommodation) {
        return count($accommodation['rooms']) > 0;
      });

      $availableOthers = $others->filter(function ($other) use ($start_date, $end_date) {
        $overlappingBookings = User_accommodation::query()
          ->where('accommodation_id', $other->id)
          ->where(function ($query) use ($start_date, $end_date) {
            $query
              ->where('start_date', '<=', $end_date)
              ->where('end_date', '>=', $start_date);
          })
          ->exists();
        return !$overlappingBookings;
      });

      return [
        'hotels' => $accommodationsWithRooms->values(),
        'others' => $availableOthers->values()
      ];
    }

    $accommodationQuery = Accommodation::query();
    $accommodations = $accommodationQuery
      ->join('owners', 'accommodations.owner_id', '=', 'owners.id')
      ->where(function ($query) use ($name, $destination, $country_id) {
        if ($name != '') {
          $query->where('accommodations.accommodation_name', 'LIKE', '%' . $name . '%');
        }
        if ($destination != '') {
          $query->where('owners.location', 'LIKE', '%' . $destination . '%');
        }
        if ($country_id != '') {
          $query->where('owners.country_id', $country_id);
        }
      })
      ->where('accommodations.accommodation_type_id', $type_id)
      ->select('accommodations.*')
      ->get();

    if ($type_id == 1) {
      $accommodationWithAvailableRooms = $accommodations->map(function ($accommodation) use ($start_date, $end_date, $people) {
        $rooms = Room::query()->where('accommodation_id', $accommodation->id)->get();

        $availableRooms = $rooms->filter(function ($room) use ($start_date, $end_date, $people) {
          if ($room->people_count < $people) {
            return false;
          }

          $overlappingBookings = User_room::query()
            ->where('room_id', $room->id)
            ->where(function ($query) use ($start_date, $end_date) {
              // Booking starts before our end date AND ends after our start date
              $query
                ->where('start_date', '<=', $end_date)
                ->where('end_date', '>=', $start_date);
            })
            ->exists();

          // Return true if room is available (no overlapping bookings)
          return !$overlappingBookings;
        });

        $accommodation['rooms'] = $availableRooms->values();
        return $accommodation;
      });

      // Filter out accommodations with no available rooms
      $accommodationsWithRooms = $accommodationWithAvailableRooms->filter(function ($accommodation) {
        return count($accommodation['rooms']) > 0;
      });
      return $accommodationsWithRooms->values();
    } else {
      $availableAccommodations = $accommodations->filter(function ($accommodation) use ($start_date, $end_date) {
        $overlappingBookings = User_accommodation::query()
          ->where('accommodation_id', $accommodation->id)
          ->where(function ($query) use ($start_date, $end_date) {
            $query
              ->where('start_date', '<=', $end_date)
              ->where('end_date', '>=', $start_date);
          })
          ->exists();
        return !$overlappingBookings;
      });

      return $availableAccommodations->values();
    }
  }

  public function check_room_availability($room_id, $start_date, $end_date)
  {
    $room = Room::query()->where('id', $room_id)->first();

    if (!$room) {
      return [
        'available' => false,
        'message' => 'Room not found'
      ];
    }

    $overlappingBookings = User_room::query()
      ->where('room_id', $room_id)
      ->where(function ($query) use ($start_date, $end_date) {
        $query
          ->where('start_date', '<=', $end_date)
          ->where('end_date', '>=', $start_date);
      })
      ->exists();

    if ($overlappingBookings) {
      return [
        'available' => false,
        'message' => 'Room is not available for the selected dates'
      ];
    }

    return [
      'available' => true,
      'message' => 'Room is available',
      'room' => $room
    ];
  }

  public function book_room($request, $id)
  {
    $user = Auth::user();
    $room = Room::query()->where('id', $id)->first();

    if (!$room) {
      return ['success' => false, 'message' => 'Room not found'];
    }

    $booking = User_room::create([
      'user_id' => $user->id,
      'room_id' => $id,
      'traveler_name' => $request['traveler_name'],
      'national_number' => $request['national_number'],
      'start_date' => $request['start_date'],
      'end_date' => $request['end_date'],
      // 'payment_id' => $request['payment_id'],
    ]);

    return [
      'message' => 'Room booked successfully',
      'booking_details' => $booking
    ];
  }

    public function check_accommodation_availability($accommodation_id, $start_date, $end_date)
  {
    $accommodation = Accommodation::query()->where('id', $accommodation_id)->first();

    if (!$accommodation) {
      return [
        'available' => false,
        'message' => 'Accommodation not found'
      ];
    }

    $overlappingBookings = User_accommodation::query()
      ->where('accommodation_id', $accommodation_id)
      ->where(function ($query) use ($start_date, $end_date) {
        $query
          ->where('start_date', '<=', $end_date)
          ->where('end_date', '>=', $start_date);
      })
      ->exists();

    if ($overlappingBookings) {
      return [
        'available' => false,
        'message' => 'This accommodation is not available for the selected dates'
      ];
    }

    return [
      'available' => true,
      'message' => 'Accommodation is available',
      'accommodation' => $accommodation
    ];
  }

  public function book_accommodation($request, $id)
  {
    $user = Auth::user();
    $accommodation = Accommodation::query()->where('id', $id)->first();

    if (!$accommodation) {
      return ['success' => false, 'message' => 'This Accommodation not found'];
    }

    $booking = User_accommodation::create([
      'user_id' => $user->id,
      'accommodation_id' => $id,
      'traveler_name' => $request['traveler_name'],
      'national_number' => $request['national_number'],
      'start_date' => $request['start_date'],
      'end_date' => $request['end_date'],
      // 'payment_id' => $request['payment_id'],
    ]);

    return [
      'message' => 'Accommodation booked successfully',
      'booking_details' => $booking
    ];
  }

  // public function filterFlights($request)
  // {
  //   $start          = $request->starting_point_location;
  //   $end            = $request->landing_point_location;
  //   $isRoundTrip    = $request->is_round_trip;
  //   $passengerCount = (int) $request->passenger_count;

  //   if (!$isRoundTrip) {
  //     $date = $request->date;

  //     $flights = Flight::with(['Air_line', 'Plane', 'Seat'])
  //       ->where('starting_point_location', $start)
  //       ->where('landing_point_location', $end)
  //       ->whereDate('date', $date)
  //       ->get();

  //     $flights = $flights->filter(function ($flight) use ($passengerCount) {
  //       if (!$flight->Plane) return false;

  //       $available = $flight->Plane->seats_count - DB::table('user_flights')->where('flight_id', $flight->id)->count();
  //       return $available >= $passengerCount;
  //     })->values();

  //     return $flights->toArray();
  //   }

  //   $departureDate = $request->departure_date;
  //   $returnDate    = $request->return_date;

  //   $goFlights = Flight::with(['Air_line', 'Plane', 'Seat'])
  //     ->where('starting_point_location', $start)
  //     ->where('landing_point_location', $end)
  //     ->whereDate('date', $departureDate)
  //     ->get();

  //   $returnFlights = Flight::with(['Air_line', 'Plane', 'Seat'])
  //     ->where('starting_point_location', $end)
  //     ->where('landing_point_location', $start)
  //     ->whereDate('date', $returnDate)
  //     ->get();

  //   $goFlights = $goFlights->filter(function ($flight) use ($passengerCount) {
  //     if (!$flight->Plane) return false;
  //     $available = $flight->Plane->seats_count - DB::table('user_flights')->where('flight_id', $flight->id)->count();
  //     return $available >= $passengerCount;
  //   })->values();

  //   $returnFlights = $returnFlights->filter(function ($flight) use ($passengerCount) {
  //     if (!$flight->Plane) return false;
  //     $available = $flight->Plane->seats_count - DB::table('user_flights')->where('flight_id', $flight->id)->count();
  //     return $available >= $passengerCount;
  //   })->values();

  //   if ($goFlights->isEmpty() || $returnFlights->isEmpty()) {
  //     return [];
  //   }

  //   $roundTrips = [];
  //   foreach ($goFlights as $go) {
  //     foreach ($returnFlights as $ret) {
  //       $roundTrips[] = ['go' => $go, 'return' => $ret];
  //     }
  //   }

  //   return $roundTrips;
  // }

  public function filterFlights($request)
  {
    $start          = $request->starting_point_location;
    $end            = $request->landing_point_location;
    $isRoundTrip    = $request->is_round_trip;
    $passengerCount = (int) $request->passenger_count;
    $sortBy         = $request->sort_by;

    if (!$isRoundTrip) {
      $date = $request->date;

      $flights = Flight::with(['Air_line', 'Plane', 'Seat'])
        ->where('starting_point_location', $start)
        ->where('landing_point_location', $end)
        ->whereDate('date', $date)
        ->get();

      $flights = $flights->filter(function ($flight) use ($passengerCount) {
        if (!$flight->Plane) return false;

        $available = $flight->Plane->seats_count
          - DB::table('user_flights')->where('flight_id', $flight->id)->count();

        return $available >= $passengerCount;
      })->values();

      $flights = $this->sortFlights($flights, $sortBy);

      return $flights->toArray();
    }

    $departureDate = $request->departure_date;
    $returnDate    = $request->return_date;

    $goFlights = Flight::with(['Air_line', 'Plane', 'Seat'])
      ->where('starting_point_location', $start)
      ->where('landing_point_location', $end)
      ->whereDate('date', $departureDate)
      ->get();

    $returnFlights = Flight::with(['Air_line', 'Plane', 'Seat'])
      ->where('starting_point_location', $end)
      ->where('landing_point_location', $start)
      ->whereDate('date', $returnDate)
      ->get();

    $goFlights = $goFlights->filter(function ($flight) use ($passengerCount) {
      if (!$flight->Plane) return false;
      $available = $flight->Plane->seats_count
        - DB::table('user_flights')->where('flight_id', $flight->id)->count();
      return $available >= $passengerCount;
    })->values();

    $returnFlights = $returnFlights->filter(function ($flight) use ($passengerCount) {
      if (!$flight->Plane) return false;
      $available = $flight->Plane->seats_count
        - DB::table('user_flights')->where('flight_id', $flight->id)->count();
      return $available >= $passengerCount;
    })->values();

    if ($goFlights->isEmpty() || $returnFlights->isEmpty()) {
      return [];
    }

    $roundTrips = [];
    foreach ($goFlights as $go) {
      foreach ($returnFlights as $ret) {
        $roundTrips[] = [
          'go'     => $go,
          'return' => $ret
        ];
      }
    }

    $roundTrips = collect($roundTrips);

    if ($sortBy === 'price') {
      $roundTrips = $roundTrips->sortBy(function ($trip) {
        $goPrice = $trip['go']->offer_price ?: $trip['go']->price;
        $retPrice = $trip['return']->offer_price ?: $trip['return']->price;
        return $goPrice + $retPrice;
      })->values();
    } elseif ($sortBy === 'shortest') {
      $roundTrips = $roundTrips->sortBy(function ($trip) {
        return $trip['go']->estimated_time + $trip['return']->estimated_time;
      })->values();
    }

    return $roundTrips->toArray();
  }

  private function sortFlights($flights, $sortBy)
  {
    if ($sortBy === 'price') {
      return $flights->sortBy(function ($flight) {
        return $flight->offer_price ?: $flight->price;
      })->values();
    }

    if ($sortBy === 'shortest') {
      return $flights->sortBy('estimated_time')->values();
    }

    return $flights;
  }

  public function searchVehicles(array $filters)
  {
    $query = Vehicle::with([
      'car_type:id,name',
      'vehicle_owner.owner.user:id,email,phone_number',
      'vehicle_owner.owner.country:id,name'
    ])->select(
      'id',
      'vehicle_owner_id',
      'car_type_id',
      'car_discription',
      'people_count',
      'name'
    );

    if (!empty($filters['location'])) {
      $query->whereHas('vehicle_owner.owner', function ($q) use ($filters) {
        $q->where('location', 'like', '%' . $filters['location'] . '%');
      });
    }

    if (!empty($filters['vehicle_name'])) {
      $query->where('name', 'like', '%' . $filters['vehicle_name'] . '%');
    }

    if (!empty($filters['car_type'])) {
      $query->where('car_type_id', $filters['car_type']);
    }

    if (!empty($filters['people_count'])) {
      $query->where('people_count', $filters['people_count']);
    }

    return $query->get()->map(function ($vehicle) {
      return [
        'id'              => $vehicle->id,
        'car_discription' => $vehicle->car_discription,
        'people_count'    => $vehicle->people_count,
        'name'            => $vehicle->name,
        'car_type_name'   => $vehicle->car_type?->name,
        'vehicle_owner'   => [
          'id'         => $vehicle->vehicle_owner->id ?? null,
          'owner_name' => $vehicle->vehicle_owner->owner_name ?? null,
          'location'   => $vehicle->vehicle_owner->owner->location ?? null,
          'user'       => [
            'email'        => $vehicle->vehicle_owner->owner->user->email ?? null,
            'phone_number' => $vehicle->vehicle_owner->owner->user->phone_number ?? null,
          ],
          'country'    => [
            'name' => $vehicle->vehicle_owner->owner->country->name ?? null,
          ],
        ],
      ];
    });
  }

  // public function filterActivities(array $filters)
  // {
  //   $query = Activity_owner::query()
  //     ->join('activities', 'activity_owners.activity_id', '=', 'activities.id')
  //     ->join('owners', 'activity_owners.owner_id', '=', 'owners.id')
  //     ->join('users', 'owners.user_id', '=', 'users.id')
  //     ->join('countries', 'owners.country_id', '=', 'countries.id')
  //     ->select(
  //       'activity_owners.owner_id',
  //       'owners.description',
  //       'owners.location',
  //       'activities.name as activity_name',
  //       'countries.name as country_name',
  //       'users.email',
  //       'users.phone_number'
  //     );

  //   if (!empty($filters['activity_name'])) {
  //     $query->where('activities.name', 'like', "%{$filters['activity_name']}%");
  //   }

  //   if (!empty($filters['country_name'])) {
  //     $query->where('countries.name', 'like', "%{$filters['country_name']}%");
  //   }

  //   if (!empty($filters['location'])) {
  //     $query->where('owners.location', 'like', "%{$filters['location']}%");
  //   }

  //   $results = $query->get();

  //   if ($results->isEmpty()) {
  //     return [
  //       'message' => 'Unfortunately, there are no activities at the moment.'
  //     ];
  //   }

  //   return $results;
  // }


  public function filterActivities(array $filters)
  {
    $query = Activity_owner::query()
      ->join('activities', 'activity_owners.activity_id', '=', 'activities.id')
      ->join('owners', 'activity_owners.owner_id', '=', 'owners.id')
      ->join('users', 'owners.user_id', '=', 'users.id')
      ->join('countries', 'owners.country_id', '=', 'countries.id')
      ->select(
        'activity_owners.owner_id',
        'owners.description',
        'owners.location',
        'activities.name as activity_name',
        'countries.name as country_name',
        'users.email',
        'users.phone_number'
      );

    if (!empty($filters['activity_name'])) {
      $query->where('activities.name', 'like', "%{$filters['activity_name']}%");
    }

    if (!empty($filters['country_name'])) {
      $query->where('countries.name', 'like', "%{$filters['country_name']}%");
    }

    if (!empty($filters['location'])) {
      $query->where('owners.location', 'like', "%{$filters['location']}%");
    }

    $results = $query->get();

    if ($results->isEmpty()) {
      return [
        'message' => 'Unfortunately, there are no activities at the moment.'
      ];
    }

    return $results->map(function ($record) {
      $picture = Picture::where('owner_id', $record->owner_id)
        ->orderBy('id', 'asc')
        ->value('reference');

      return [
        'activity_name' => $record->activity_name,
        'description'   => $record->description,
        'location'      => $record->location,
        'country_name'  => $record->country_name,
        'email'         => $record->email,
        'phone_number'  => $record->phone_number,
        'picture'       => $picture ? url($picture) : null,
      ];
    });
  }

  public function getAllVehicles()
  {
    return Vehicle::select(
      'vehicles.id',
      'vehicles.name as vehicle_name',
      'vehicles.car_discription',
      'vehicles.people_count',
      'car_types.name as car_type',
      'vehicle_owners.owner_name',
      'owners.location',
      'users.email',
      'users.phone_number'
    )
      ->join('car_types', 'vehicles.car_type_id', '=', 'car_types.id')
      ->join('vehicle_owners', 'vehicles.vehicle_owner_id', '=', 'vehicle_owners.id')
      ->join('owners', 'vehicle_owners.owner_id', '=', 'owners.id')
      ->join('users', 'owners.user_id', '=', 'users.id')
      ->with(['car_picture' => function ($query) {
        $query->select('vehicle_id', 'picture_path')->limit(1); // أول صورة فقط
      }])
      ->get()
      ->map(function ($vehicle) {
        return [
          'id'            => $vehicle->id,
          'name'          => $vehicle->vehicle_name,
          'description'   => $vehicle->car_discription,
          'people_count'  => $vehicle->people_count,
          'car_type'      => $vehicle->car_type,
          'owner_name'    => $vehicle->owner_name,
          'location'      => $vehicle->location,
          'email'         => $vehicle->email,
          'phone_number'  => $vehicle->phone_number,
          'picture'       => $vehicle->car_picture->first()
            ? asset('storage/' . $vehicle->car_picture->first()->picture_path)
            : null,
        ];
      });
  }
}
