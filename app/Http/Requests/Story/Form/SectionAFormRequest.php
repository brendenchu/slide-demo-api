<?php

namespace App\Http\Requests\Story\Form;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SectionAFormRequest extends FormRequest
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
                'section_a_1' => 'nullable|boolean',
                'section_a_2' => 'nullable|boolean',
                'section_a_3' => 'nullable|boolean',
                'section_a_4' => 'nullable',
                'section_a_5' => 'nullable',
                'section_a_6' => 'nullable',
            ],
            2 => [
                'section_a_4' => ['nullable', 'required_if_accepted:section_a_1', 'numeric',
                    function ($attribute, $value, $fail): void {
                        // fail if not current year
                        if ($value !== date('Y')) {
                            $fail('This field must be the current year.');
                        }
                    },
                ],
                'section_a_5' => ['nullable', 'required_if_accepted:section_a_2', 'numeric',
                    function ($attribute, $value, $fail): void {
                        // fail if not current month
                        if ($value !== date('n') && $value !== date('m')) {
                            $fail('This field must be the current month.');
                        }
                    },
                ],
                'section_a_6' => ['nullable', 'required_if_accepted:section_a_3', 'numeric',
                    function ($attribute, $value, $fail): void {
                        // fail if not current day
                        if ($value !== date('j')) {
                            $fail('This field must be the current day.');
                        }
                    },
                ],
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
            'section_a_1.boolean' => 'This field must be a boolean.',
            'section_a_2.boolean' => 'This field must be a boolean.',
            'section_a_3.boolean' => 'This field must be a boolean.',
            'section_a_4.required_if' => 'This field is required.',
            'section_a_4.numeric' => 'This field must be a number.',
            'section_a_5.required_if' => 'This field is required.',
            'section_a_5.numeric' => 'This field must be a number.',
            'section_a_6.required_if' => 'This field is required.',
            'section_a_6.numeric' => 'This field must be a number.',
        ];
    }
}
