<?php

namespace App\Http\Controllers\Admin\User;

use Exception;
use Illuminate\Http\Request;

class SearchUserController extends BaseUserController
{
    /**
     * @throws Exception
     */
    public function __invoke(
        Request $request
    ) {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'The user does not exist.',
        ]);

        $this->setupUser($validated['email']);

        // redirect to the user profile
        return to_route('admin.users.show', $this->user->profile->public_id);
    }
}
