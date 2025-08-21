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
    switch ($action) {

      case 'filter_accommodation':
        return [
          'type_id' => 'integer|nullable',
          'country_id' => 'integer|nullable',
          'name' => 'string|nullable',
          'destination' => 'string|nullable',
          'start_date' => 'date|required',
          'end_date' => 'date|required',
          'people_count' => 'integer|nullable',
        ];

      case 'book_room':
      case 'book_accommodation':
        return [
          'stripeToken' => 'required|string',
          'amount' => 'required|integer|min:0.01',
          'traveler_name' => 'required|string',
          'national_number' => 'required|integer',
          'start_date' => 'required|date',
          'end_date' => 'required|date'
        ];

      default:
        return [];
    }
  }
}
