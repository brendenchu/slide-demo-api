<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Requests\Admin\SearchUserRequest;
use Exception;

class SearchUserController extends BaseUserController
{
    /**
     * @throws Exception
     */
    public function __invoke(
        SearchUserRequest $request
    ) {
        $this->setupUserByIdentifier($request->email);

        // redirect to the user profile
        return to_route('admin.users.show', $this->user->profile->public_id);
    }
}
