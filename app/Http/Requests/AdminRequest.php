<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
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
      case 'edit_request':
        return [
          'activity_name' => 'required|string|max:255',
        ];

      case 'admin_search':
        return [
          'country' => 'string',
          'name' => 'string',
          'category_id'  => 'numeric',
        ];

      default:
        return [];
    }
  }
}
