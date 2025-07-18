<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BrowseUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'nullable|email|exists:users,email',
            'last_name' => 'nullable|string',
            'team_name' => 'nullable|string',
            'accepted_terms' => 'nullable|boolean',
            'step' => 'nullable|string',
            'role' => 'nullable|string',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
            'per_page' => 'nullable|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
