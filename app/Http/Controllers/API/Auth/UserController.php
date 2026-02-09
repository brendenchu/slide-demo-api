<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Auth\DeleteUserRequest;
use App\Http\Requests\API\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends ApiController
{
    /**
     * Get the authenticated user
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // Load relationships for the resource
        $user->load(['profile', 'teams']);

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

        // Update profile fields
        if ($request->hasAny(['first_name', 'last_name'])) {
            $user->profile->update($request->only(['first_name', 'last_name']));

            // Sync user name from profile
            $user->update([
                'name' => trim($user->profile->first_name . ' ' . $user->profile->last_name),
            ]);
        }

        // Update email
        if ($request->filled('email')) {
            $user->update(['email' => $request->input('email')]);
        }

        // Reload relationships
        $user->refresh()->load(['profile', 'teams']);

        return $this->success([
            'user' => new UserResource($user),
        ], 'Profile updated successfully');
    }

    /**
     * Delete the authenticated user's account
     */
    public function destroy(DeleteUserRequest $request): JsonResponse
    {
        $user = $request->user();

        Auth::guard('web')->logout();

        $user->currentAccessToken()->delete();
        $user->delete();

        return $this->success(message: 'Account deleted successfully');
    }
}
