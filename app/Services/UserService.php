<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Accommodation_type;
use App\Models\Activity;
use App\Models\Activity_owner;
use App\Models\Owner;
use App\Models\Package;
use App\Models\Package_element;
use App\Models\Package_element_picture;
use App\Models\Room;
use App\Models\Tourism_company;
use App\Models\User;
use App\Models\User_accommodation;
use App\Models\User_package;
use App\Models\User_room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

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

  public function getActivity()
  {
    return DB::table('activity_owners')
      ->join('activities', 'activity_owners.activity_id', '=', 'activities.id')
      ->join('owners', 'activity_owners.owner_id', '=', 'owners.id')
      ->join('users', 'owners.user_id', '=', 'users.id')
      ->join('countries', 'owners.country_id', '=', 'countries.id')
      ->select(
        'activities.id as id',
        'activities.name as activity_name',
        'activity_owners.owner_name',
        'owners.description',
        'owners.location',
        'users.phone_number',
        'countries.name as country_name'
      )
      ->get();
  }

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
          'owners.description',
          'owners.location',
          'users.phone_number',
          'countries.name as country_name'
        )
        ->first();
      return [
        'id' => $activity->id ?? null,
        'activity_name' => $activity->name ?? null,
        'owner_name' => $record->owner_name,
        'description' => $ownerData->description ?? null,
        'location' => $ownerData->location ?? null,
        'phone_number' => $ownerData->phone_number ?? null,
        'country_name' => $ownerData->country_name ?? null,
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
        'owners.location as owner_location'
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

}
