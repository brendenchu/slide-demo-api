<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Base API Controller
 *
 * Provides standardized response methods for API endpoints
 */
class ApiController extends Controller
{
    /**
     * Return a successful response with data
     */
    protected function success(
        mixed $data = null,
        string $message = '',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== '') {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response
     */
    protected function error(
        string $message,
        int $statusCode = 400,
        ?array $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a resource response
     */
    protected function resource(
        JsonResource $resource,
        string $message = '',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'data' => $resource,
        ];

        if ($message !== '') {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a paginated resource collection
     */
    protected function paginated(
        ResourceCollection $collection,
        string $message = ''
    ): JsonResponse {
        $response = $collection->response()->getData(true);

        $response['success'] = true;

        if ($message !== '') {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Return a created response (201)
     */
    protected function created(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a no content response (204)
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an unauthorized response (401)
     */
    protected function unauthorized(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->error($message, 401);
    }

    /**
     * Return a forbidden response (403)
     */
    protected function forbidden(
        string $message = 'Forbidden'
    ): JsonResponse {
        return $this->error($message, 403);
    }

    /**
     * Return a not found response (404)
     */
    protected function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->error($message, 404);
    }

    /**
     * Return a validation error response (422)
     */
    protected function validationError(
        array $errors,
        string $message = 'The given data was invalid.'
    ): JsonResponse {
        return $this->error($message, 422, $errors);
    }
}
