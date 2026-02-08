<?php

namespace App\Http\Requests\API\Auth;

use App\Support\SafeNames;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', Rule::in(SafeNames::FIRST_NAMES)],
            'last_name' => ['required', 'string', Rule::in(SafeNames::LAST_NAMES)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'first_name.in' => 'Please select a valid first name from the list',
            'last_name.required' => 'Last name is required',
            'last_name.in' => 'Please select a valid last name from the list',
        ];
    }
}
