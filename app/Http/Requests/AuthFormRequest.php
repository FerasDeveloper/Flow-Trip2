<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthFormRequest extends FormRequest
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
      return match($this->route()->getActionMethod()) {
        'user_Register' => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|string',
            'role_id' => 'required|integer',
            'phone_number' => 'nullable|unique:users|string',
        ],
        'login' => [
            'email' => 'required|email',
            'password' => 'required|string',
        ],
        'verification' => [
            'verification_code' => 'required|digits:6',
        ],
        'reset_password' => [
            'new_password' => 'required|min:8|string',
        ],
        'create_owner' => [
            'owner_category_id' => 'required|integer',
            'country_id' => 'required|integer',
            'location' => 'required|string',
            'description' => 'required|string',
            'business_name' => 'required|string',
            'activity_name' => 'nullable|string',
            'accommodation_type' => 'nullable|string',
        ],
        default => [],
      };
    }
}
