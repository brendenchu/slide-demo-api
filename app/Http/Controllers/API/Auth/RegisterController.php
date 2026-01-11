<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\Role;
use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends ApiController
{
    /**
     * User Registration
     *
     * Register a new user account and return an API token. New users are automatically assigned the 'client' role.
     *
     * @unauthenticated
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": "1",
     *       "name": "New User",
     *       "email": "newuser@example.com",
     *       "roles": ["client"],
     *       "permissions": ["view-project", "create-project", "update-project"]
     *     },
     *     "token": "1|AbCdEf..."
     *   },
     *   "message": "Registration successful"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "The given data was invalid",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        // Create user
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Assign default role (client)
        $user->assignRole(Role::Client->value);

        // Create profile if needed
        if (! $user->profile) {
            $user->profile()->create([]);
        }

        // Load relationships for the resource
        $user->load(['profile', 'roles', 'permissions']);

        // Create API access token
        $token = $user->createToken('api-client')->plainTextToken;

        return $this->created([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Registration successful');
    }
}
