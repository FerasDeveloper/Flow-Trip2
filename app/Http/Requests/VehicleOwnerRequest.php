<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleOwnerRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $action = $this->route()->getActionMethod();

    return match ($action) {
      'createVehicly' => $this->createVehiclyRules(),
      'editVehicly' => $this->editVehiclyRules(),
      'deleteVehicly' => $this->deleteVehiclyRules(),
      'createPictureCar' => $this->createPictureCarRules(),
      'getAllPicture' => $this->getAllPictureRules(),
      default => [],
    };
  }

  private function createVehiclyRules(): array
  {
    return [
      'car_type_id' => 'required|integer',
      'car_discription' => 'nullable|string|max:255',
      'people_count' => 'required|integer|min:1',
      'name' => 'required|string|max:255',
    ];
  }
  private function editVehiclyRules(): array
  {
    return [
      'car_discription' => 'nullable|string|max:255',
      'people_count' => 'nullable|integer|min:1',
    ];
  }
  private function deleteVehiclyRules(): array
  {
    return [];
  }
  private function createPictureCarRules(): array
  {
    return [
      'vehicle_id' => 'required|exists:vehicles,id',
      'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ];
  }
  private function getAllPictureRules(): array
  {
    return [];
  }
  public function messages(): array
  {
    $action = $this->route()->getActionMethod();

    switch ($action) {
      case 'createVehicly':
        return [
          'car_type_id.required' => 'Car type is required.',
          'car_type_id.integer' => 'Car type must be an integer.',
          'people_count.required' => 'People count is required.',
          'people_count.integer' => 'People count must be a number.',
          'people_count.min' => 'People count must be at least 1.',
          'name.required' => 'Vehicle name is required.',
          'name.string' => 'Vehicle name must be a string.',
          'name.max' => 'Vehicle name must not exceed 255 characters.',
          'car_discription.string' => 'Description must be a string.',
          'car_discription.max' => 'Description must not exceed 255 characters.',
        ];

      case 'editVehicly':
        return [
          'car_discription.string' => 'Description must be a string.',
          'car_discription.max' => 'Description must not exceed 255 characters.',
          'people_count.integer' => 'People count must be a number.',
          'people_count.min' => 'People count must be at least 1.',
        ];
      case 'createPictureCar':
        return [
          'vehicle_id.required' => 'Vehicle ID is required.',
          'vehicle_id.exists' => 'The selected vehicle does not exist.',
          'picture.required' => 'Please upload a picture.',
          'picture.image' => 'The uploaded file must be an image.',
          'picture.mimes' => 'Allowed image types: jpeg, png, jpg, gif, svg.',
          'picture.max' => 'Maximum image size is 2MB.',
        ];
      case 'getAllPicture':
        return [];
      case 'deleteVehicly':
        return [];
      default:
        return [];
    }
  }
}
