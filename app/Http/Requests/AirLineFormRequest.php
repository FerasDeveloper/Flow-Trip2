<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AirLineFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        return match ($action) {
            'add_plane' => [
                'plane_type_id' => 'required|integer',
                'seats_count' => 'required|integer',
                'plane_shape_diagram' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'status' => 'required|string',
            ],
            'edit_plane' => [
                'plane_type_id' => 'required|integer',
                'seats_count' => 'required|integer',
                'plane_shape_diagram' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status' => 'required|string',
            ],
            'add_flight' => [
                'plane_id' => 'required|integer',
                'price' => 'required|numeric',
                'flight_number' => 'required|string',
                'starting_point_location' => 'required|string',
                'landing_point_location' => 'required|string',
                'starting_airport' => 'required|string',
                'landing_airport' => 'required|string',
                'start_time' => 'required|string',
                'land_time' => 'required|string',
                'estimated_time' => 'required|string',
                'date' => 'required|date',
            ],
            'edit_flight' => [
                'plane_id' => 'required|integer',
                'price' => 'required|numeric',
                'offer_price' => 'nullable|numeric',
                'flight_number' => 'required|string',
                'starting_point_location' => 'required|string',
                'landing_point_location' => 'required|string',
                'starting_airport' => 'required|string',
                'landing_airport' => 'required|string',
                'start_time' => 'required|string',
                'land_time' => 'required|string',
                'estimated_time' => 'required|string',
                'date' => 'required|date',
            ],
            'edit_seats' => [
                'seat_ids' => 'required|array',
                'new_price' => 'required|numeric|min:0',
            ],
            'search_reservations_by_name' => [
                'name' => 'required|string',
            ],
            default => [],
        };
    }
}
