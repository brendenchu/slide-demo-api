<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * Get the authenticated user
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // Load relationships for the resource
        $user->load(['profile', 'teams', 'roles', 'permissions']);

        return $this->success([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the authenticated user's profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        // Update user fields
        $user->update($request->only(['name', 'email']));

        // Reload relationships
        $user->refresh()->load(['profile', 'teams', 'roles', 'permissions']);

        return $this->success([
            'user' => new UserResource($user),
        ], 'Profile updated successfully');
    }
}
