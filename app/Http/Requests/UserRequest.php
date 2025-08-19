<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $action = $this->route()->getActionMethod();

    return match ($action) {
      'searchVehicles' => $this->searchVehiclesRules(),
      'filterFlights'  => $this->filterFlightsRules(),
      'filterActivities' => $this->filterActivitiesRules(),
      default => [],
    };
  }

  private function searchVehiclesRules(): array
  {
    return [
      'location'     => ['nullable', 'string'],
      'vehicle_name' => ['nullable', 'string'],
      'car_type'     => ['nullable', 'integer', 'exists:car_types,id'],
      'people_count' => ['nullable', 'integer', 'min:1'],
    ];
  }

  private function filterFlightsRules(): array
  {
    return [
      'starting_point_location' => ['required', 'string'],
      'landing_point_location'  => ['required', 'string'],
      'passenger_count'         => ['required', 'integer', 'min:1'],
      'is_round_trip'           => ['required', 'boolean'],
      'date'                    => ['required_if:is_round_trip,false', 'date'],
      'departure_date'          => ['required_if:is_round_trip,true', 'date'],
      'return_date'             => ['required_if:is_round_trip,true', 'date'],
    ];
  }
  private function filterActivitiesRules(): array
  {
    return [
      'activity_name' => ['nullable', 'string'],
      'country_name'  => ['nullable', 'string'],
      'location'      => ['nullable', 'string'],
    ];
  }


  public function messages(): array
  {
    $action = $this->route()->getActionMethod();

    switch ($action) {
      case 'searchVehicles':
        return [
          'location.string'        => 'The location must be a string.',
          'vehicle_name.string'    => 'The vehicle name must be a string.',
          'car_type.integer'       => 'The car type must be an integer.',
          'car_type.exists'        => 'The selected car type is invalid.',
          'people_count.integer'   => 'The people count must be a number.',
          'people_count.min'       => 'The people count must be at least 1.',
        ];

      case 'filterFlights':
        return [
          'starting_point_location.required' => 'Starting location is required.',
          'starting_point_location.string'   => 'Starting location must be a string.',
          'landing_point_location.required'  => 'Landing location is required.',
          'landing_point_location.string'    => 'Landing location must be a string.',
          'passenger_count.required'         => 'Passenger count is required.',
          'passenger_count.integer'          => 'Passenger count must be a number.',
          'passenger_count.min'              => 'Passenger count must be at least 1.',
          'is_round_trip.required'           => 'Round trip status is required.',
          'is_round_trip.boolean'            => 'Round trip must be true or false.',
          'date.required_if'                 => 'Date is required for one-way trips.',
          'departure_date.required_if'       => 'Departure date is required for round trips.',
          'return_date.required_if'          => 'Return date is required for round trips.',
        ];
      case 'filterActivities':
        return [
          'activity_name.string' => 'Activity name must be a string.',
          'country_name.string'  => 'Country name must be a string.',
          'location.string'      => 'Location must be a string.',

        ];

      default:
        return [];
    }
  }
}
