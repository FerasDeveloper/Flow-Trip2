<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccommodationRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    // نحصل على اسم الميثود الموجود في الراوت
    $action = $this->route()->getActionMethod();

    switch ($action) {
      case 'filter_name_accommodation':
        return [
          'name' => 'required|string|max:255',
        ];

      case 'add_room':
        return [
          'price' => 'required|numeric|min:1',
          'area' => 'required|numeric|min:1',
          'people_count' => 'required|integer|min:1',
          'description' => 'required|string',
          'room_number' => 'required|numeric'
        ];

      case 'edit_room':
        return [
          'price' => 'required|numeric|min:1',
          'area' => 'required|numeric|min:1',
          'people_count' => 'required|integer|min:1',
          'description' => 'required',
          'room_number' => 'required|numeric'
        ];

      default:
        return [];
    }
  }

  public function messages()
  {
    $action = $this->route()->getActionMethod();
    switch ($action) {
      case 'add_room':
        return [
          'price.min' => 'The price must be a positive value greater than zero.',
          'area.min' => 'The area must be a positive value greater than zero.',
          'people_count.min' => 'The number of people must be a positive value greater than zero.',
        ];

      case 'edit_room':
        return [
          'price.min' => 'The price must be a positive value greater than zero.',
          'area.min' => 'The area must be a positive value greater than zero.',
          'people_count.min' => 'The number of people must be a positive value greater than zero.',
        ];
    }
  }
}
