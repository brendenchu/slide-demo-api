<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends ApiController
{
    /**
     * User Registration
     *
     * Register a new user account and return an API token.
     *
     * @unauthenticated
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": "1",
     *       "name": "New User",
     *       "email": "newuser@example.com"
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
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');

        // Generate unique email from names
        $suffix = Str::lower(Str::random(4));
        $email = Str::lower($firstName) . '.' . Str::lower($lastName) . '.' . $suffix . '@example.com';

        // Create user
        $user = User::create([
            'name' => "$firstName $lastName",
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        // Create profile with names
        if (! $user->profile) {
            $user->profile()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        }

        // Load relationships for the resource
        $user->load(['profile']);

        // Create API access token
        $token = $user->createToken('api-client')->plainTextToken;

        return $this->created([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Registration successful');
    }
}
