<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;

class DemoStatusController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        $isEnabled = (bool) config('demo.enabled');

        return $this->success([
            'demo_mode' => $isEnabled,
            'limits' => $isEnabled ? config('demo.limits') : null,
        ]);
    }
}
