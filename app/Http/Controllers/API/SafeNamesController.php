<?php

namespace App\Http\Controllers\API;

use App\Support\SafeNames;
use Illuminate\Http\JsonResponse;

class SafeNamesController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        return $this->success([
            'first_names' => SafeNames::FIRST_NAMES,
            'last_names' => SafeNames::LAST_NAMES,
        ]);
    }
}
