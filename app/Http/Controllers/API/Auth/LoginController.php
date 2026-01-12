<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends ApiController
{
    /**
     * User Login
     *
     * Authenticate a user and return an API token for subsequent requests.
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": "1",
     *       "name": "Test User",
     *       "email": "test@example.com",
     *       "roles": ["client"],
     *       "permissions": ["view-project", "create-project"]
     *     },
     *     "token": "1|AbCdEf..."
     *   },
     *   "message": "Login successful"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid credentials"
     * }
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return $this->unauthorized('Invalid credentials');
        }

        $user = Auth::user();

        // Load relationships for the resource
        $user->load(['profile', 'roles', 'permissions']);

        // Create API access token
        $token = $user->createToken('api-client')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Login successful');
    }
}
