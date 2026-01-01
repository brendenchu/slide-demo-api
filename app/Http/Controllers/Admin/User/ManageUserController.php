<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Resources\Story\TokenResource;
use App\Http\Resources\UserResource;
use App\Models\Account\Profile;
use Exception;
use Illuminate\Http\Request;
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
        $this->setupUser($profile->public_id);

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
    public function store(Request $request)
    {
        // validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|exists:roles,name',
        ]);

        // create the user
        $this->accountService->createUser($validated);

        // send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
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
