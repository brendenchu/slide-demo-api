<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class AcceptTermsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'accepted'],
        ];
    }
}
