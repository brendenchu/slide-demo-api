<?php

namespace App\Http\Requests\Story\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SectionBFormRequest extends FormRequest
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
                'section_b_1' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // 1 + 1 = 2
                        if (intval($value) !== 2) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
                'section_b_2' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // 2 - 6 = -4
                        if (intval($value) !== -4) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
                'section_b_3' => ['required', 'numeric',
                    // 3 * 3 = 9
                    function ($attribute, $value, $fail) {
                        if (intval($value) !== 9) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            2 => [
                'section_b_4' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // 12 / 4 = 3
                        if (intval($value) !== 3) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
                'section_b_5' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // 3 ^ 3 = 27
                        if (intval($value) !== 27) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
                'section_b_6' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // 5! = 120
                        if (intval($value) !== 120) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            3 => [
                'section_b_7' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // sides of a heptagon
                        if (intval($value) !== 7) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
                'section_b_8' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // degrees in a right angle
                        if (intval($value) !== 90) {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
                'section_b_9' => ['required', 'numeric',
                    function ($attribute, $value, $fail) {
                        // days in a leap year
                        if (intval($value) !== 366) {
                            $fail('Sorry, that is incorrect.');
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
            'section_b_1.required' => 'This field is required.',
            'section_b_1.string' => 'This field must be a string.',
            'section_b_1.max' => 'This field must not exceed 255 characters.',
            'section_b_2.required' => 'This field is required.',
            'section_b_2.string' => 'This field must be a string.',
            'section_b_2.max' => 'This field must not exceed 255 characters.',
            'section_b_3.required' => 'This field is required.',
            'section_b_3.string' => 'This field must be a string.',
            'section_b_3.max' => 'This field must not exceed 255 characters.',
            'section_b_4.required' => 'This field is required.',
            'section_b_4.string' => 'This field must be a string.',
            'section_b_4.max' => 'This field must not exceed 255 characters.',
            'section_b_5.required' => 'This field is required.',
            'section_b_5.string' => 'This field must be a string.',
            'section_b_5.max' => 'This field must not exceed 255 characters.',
            'section_b_6.required' => 'This field is required.',
            'section_b_6.string' => 'This field must be a string.',
            'section_b_6.max' => 'This field must not exceed 255 characters.',
            'section_b_7.required' => 'This field is required.',
            'section_b_7.string' => 'This field must be a string.',
            'section_b_7.max' => 'This field must not exceed 255 characters.',
            'section_b_8.required' => 'This field is required.',
            'section_b_8.string' => 'This field must be a string.',
            'section_b_8.max' => 'This field must not exceed 255 characters.',
            'section_b_9.required' => 'This field is required.',
            'section_b_9.string' => 'This field must be a string.',
            'section_b_9.max' => 'This field must not exceed 255 characters.',
        ];
    }
}
