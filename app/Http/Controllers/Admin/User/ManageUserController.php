<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Resources\Story\TokenResource;
use App\Http\Resources\UserResource;
use App\Models\Account\Profile;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ManageUserController extends BaseUserController
{
    /**
     * @throws Exception
     */
    public function index()
    {
        return to_route('admin.dashboard')->with('warning', 'This page is not accessible.');
    }

    /**
     * @throws Exception
     */
    public function show(Profile $profile)
    {
        $this->setupUser($profile);

        return Inertia::render('Admin/ShowUser', [
            'user' => UserResource::make($this->user),
            'tokens' => TokenResource::collection($this->tokens),
        ]);
    }

    /**
     * Create a new user.
     */
    public function create()
    {
        return Inertia::render('Admin/CreateUser');
    }

    /**
     * @throws Exception
     */
    public function store(CreateUserRequest $request)
    {
        // create the user
        $this->accountService->createUser($request->validated());

        // send the password reset link
        $status = Password::sendResetLink(
            $request->validated()
        );

        // redirect back with status
        if ($status == Password::RESET_LINK_SENT) {
            return to_route(
                'admin.dashboard'
            )->with('info', __($status));
        }

        // throw validation exception
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);

    }

    /**
     * @throws Exception
     */
    public function edit(Profile $profile): never
    {
        dd('edit user');
    }
}
