<?php

namespace App\Http\Requests\Story;

use App\Http\Requests\Story\v1\IntroFormRequest;
use App\Http\Requests\Story\v1\SectionAFormRequest;
use App\Http\Requests\Story\v1\SectionBFormRequest;
use App\Http\Requests\Story\v1\SectionCFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoryFormRequest extends FormRequest
{
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
        return match ($this->input('step.id')) {
            'intro' => (new IntroFormRequest((int) $this->input('page')))->rules(),
            'section-a' => (new SectionAFormRequest((int) $this->input('page')))->rules(),
            'section-b' => (new SectionBFormRequest((int) $this->input('page')))->rules(),
            'section-c' => (new SectionCFormRequest((int) $this->input('page')))->rules(),
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
        return match ($this->input('step.id')) {
            'intro' => (new IntroFormRequest((int) $this->input('page')))->messages(),
            'section-a' => (new SectionAFormRequest((int) $this->input('page')))->messages(),
            'section-b' => (new SectionBFormRequest((int) $this->input('page')))->messages(),
            'section-c' => (new SectionCFormRequest((int) $this->input('page')))->messages(),
            default => [],
        };
    }
}
