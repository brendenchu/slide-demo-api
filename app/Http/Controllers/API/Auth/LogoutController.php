<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends ApiController
{
    /**
     * User Logout
     *
     * Logout the authenticated user by deleting their current API token. The token used for this request will be invalidated.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": null,
     *   "message": "Logout successful"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Delete current access token
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout successful');
    }
}
