<?php

namespace App\Http\Requests\API\Story;

use Illuminate\Foundation\Http\FormRequest;

class SaveResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step' => ['required', 'string'],
            'responses' => ['required', 'array'],
        ];
    }
}
