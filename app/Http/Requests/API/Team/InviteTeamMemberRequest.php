<?php

namespace App\Http\Requests\API\Team;

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $team = Team::where('public_id', $this->route('teamId'))->first();

        return $team && $team->isAdmin($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $team = Team::where('public_id', $this->route('teamId'))->first();
        $allowedRoles = $team && $team->isOwner($this->user())
            ? TeamRole::assignable()
            : [TeamRole::Member->value];

        return [
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $team = Team::where('public_id', $this->route('teamId'))->first();
        $isOwner = $team && $team->isOwner($this->user());

        return [
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.exists' => 'This email is not associated with a registered user.',
            'role.required' => 'A role is required.',
            'role.in' => $isOwner
                ? 'The role must be either admin or member.'
                : 'You can only invite users as members.',
        ];
    }
}
