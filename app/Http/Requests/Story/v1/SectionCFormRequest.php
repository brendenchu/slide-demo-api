<?php

namespace App\Http\Requests\Story\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SectionCFormRequest extends FormRequest
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
                'section_c_1' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of France
                        if ($value !== 'Paris') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            2 => [
                'section_c_2' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of Japan
                        if ($value !== 'Tokyo') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            3 => [
                'section_c_3' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of Australia
                        if ($value !== 'Canberra') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            4 => [
                'section_c_4' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of Canada
                        if ($value !== 'Ottawa') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            5 => [
                'section_c_5' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of India
                        if ($value !== 'New Delhi') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            6 => [
                'section_c_6' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of Brazil
                        if ($value !== 'Brasilia') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            7 => [
                'section_c_7' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of Denmark
                        if ($value !== 'Copenhagen') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            8 => [
                'section_c_8' => ['required',
                    function ($attribute, $value, $fail) {
                        // Capital city of Kenya
                        if ($value !== 'Nairobi') {
                            $fail('Sorry, that is incorrect.');
                        }
                    },
                ],
            ],
            9 => [
                'section_c_9' => ['required',
                    function ($attribute, $value, $fail) {
                        // Not a Capital city of South Africa
                        if ($value !== 'Johannesburg') {
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
            'section_c_1.required' => 'Please select one of the options.',
            'section_c_2.required' => 'Please select one of the options.',
            'section_c_3.required' => 'Please select one of the options.',
            'section_c_4.required' => 'Please select one of the options.',
            'section_c_5.required' => 'Please select one of the options.',
            'section_c_6.required' => 'Please select one of the options.',
            'section_c_7.required' => 'Please select one of the options.',
            'section_c_8.required' => 'Please select one of the options.',
            'section_c_9.required' => 'Please select one of the options.',
        ];
    }
}
