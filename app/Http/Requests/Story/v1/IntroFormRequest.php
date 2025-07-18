<?php

namespace App\Http\Requests\Story\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IntroFormRequest extends FormRequest
{
    public function __construct(
        private readonly int $page
    ) {
        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return match ($this->page) {
            1 => [
                'intro_1' => 'required|string|max:255',
                'intro_2' => 'required|string|max:255',
                'intro_3' => 'required|string|max:255',
            ],
            default => [],
        };
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'intro_1.required' => 'This field is required.',
            'intro_1.string' => 'This field must be a string.',
            'intro_1.max' => 'This field must not exceed 255 characters.',
            'intro_2.required' => 'This field is required.',
            'intro_2.string' => 'This field must be a string.',
            'intro_2.max' => 'This field must not exceed 255 characters.',
            'intro_3.required' => 'This field is required.',
            'intro_3.string' => 'This field must be a string.',
            'intro_3.max' => 'This field must not exceed 255 characters.',
        ];
    }
}
