<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TourismCompanyRequist extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $action = $this->route()->getActionMethod();

    return match ($action) {
      'editPackage' => $this->editPackageRules(),
      'deletePackage' => $this->deletePackageRules(),
      'getPackagesfortourism' => $this->getPackagesRules(),
      'addPackageElement' => $this->addPackageElementRules(),
      'editPackageElement' => $this->editPackageElementRules(),
      'addPictureElement' => $this->addPictureElementRules(),
      'getElementPackageById' => $this->getElementPackageByIdRules(),
      default => [],
    };
  }

  private function editPackageRules(): array
  {
    return [
      'discription' => 'nullable|string',
      'total_price' => 'nullable|numeric',
      'checked' => 'nullable|boolean',
      'package_picture' => 'nullable|file|image',
    ];
  }

  private function deletePackageRules(): array
  {
    return [];
  }
  private function getPackagesRules(): array
  {
    return [];
  }

  private function addPackageElementRules(): array
  {
    return [
      'name' => 'required|string',
      'type' => 'required|string',
      'discription' => 'required|string',
      'pictures.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
    ];
  }

  private function editPackageElementRules(): array
  {
    return [
      'name' => 'nullable|string',
      'type' => 'nullable|string',
      'discription' => 'nullable|string',
    ];
  }
  private function addPictureElementRules(): array
  {
    return [
      'picture' => 'required|file|image',
    ];
  }
  private function getElementPackageByIdRules(): array
  {
    return [];
  }
  public function messages(): array
  {
    $action = $this->route()->getActionMethod();

    switch ($action) {
      case 'editPackage':
        return [
          'discription.string' => 'The description must be a string.',
          'total_price.numeric' => 'The total price must be a number.',
          'checked.boolean' => 'The checked field must be true or false.',
          'package_picture.image' => 'The uploaded file must be a valid image.',
          'package_picture.file' => 'The uploaded file must be a valid file.',
        ];

      case 'addPackageElement':
        return [
          'name.required' => 'The name field is required.',
          'type.required' => 'The type field is required.',
          'discription.required' => 'The description field is required.',
          'pictures.*.image' => 'Each uploaded file must be an image.',
          'pictures.*.mimes' => 'Each image must be of type jpeg, png, jpg, gif, or svg.',
        ];
      case 'editPackageElement':
        return [
          'name.string' => 'The name must be a string.',
          'type.string' => 'The type must be a string.',
          'discription.string' => 'The description must be a string.',
        ];
      case 'addPictureElement':
        return [
          'picture.required' => 'Please upload a picture.',
          'picture.file' => 'The uploaded file must be a valid file.',
          'picture.image' => 'The uploaded file must be an image.',
        ];
      case 'deletePackage':
      case 'getPackagesfortourism':
      case 'getElementPackageById':

        return [];

      default:
        return [];
    }
  }
}
