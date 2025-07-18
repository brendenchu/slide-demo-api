<?php

namespace App\Http\Controllers\Admin\User;

use App\Models\Account\Profile;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AdminPasswordResetController extends BaseUserController
{
    /**
     * @throws Exception
     */
    public function __invoke(Profile $profile)
    {
        $this->setupUser($profile->public_id);

        // send the password reset link
        $status = Password::sendResetLink(
            $this->user->only('email')
        );

        // redirect back with status
        if ($status == Password::RESET_LINK_SENT) {
            return to_route(
                'admin.users.show', $this->user->profile->public_id
            )->with('info', __($status));
        }

        // throw validation exception
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);

    }
}
