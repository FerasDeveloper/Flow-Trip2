<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneralTaskFormRequest extends FormRequest
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
      'add_picture' => [
        'picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
      ],
      'add_service' => [
        'services' => 'required|array',
        'services.*' => 'string',
      ],
      'edit_profile' => (function () {
        $userId = auth()->id();
        $emailRules = ['required', 'email'];
        $phoneRules = ['required', 'numeric'];

        $user = User::query()->find($userId);

        if ($this->input('email') !== $user?->email) {
          $emailRules[] = Rule::unique('users', 'email')->ignore($userId);
        }

        if ($this->input('phone_number') !== $user?->phone_number) {
          $phoneRules[] = Rule::unique('users', 'phone_number')->ignore($userId);
        }

        return [
          'name' => 'required|string',
          'email' => $emailRules,
          'phone_number' => $phoneRules,
          'description' => 'required',
          'location' => 'required',
          'country_id' => 'required|numeric'
        ];
      })(),
      'rate_owner' => [
        'owner_id' => 'required|exists:users,id',
        'rating'   => 'required|numeric|min:1|max:5',
      ],
      default => [],
    };
  }
}
