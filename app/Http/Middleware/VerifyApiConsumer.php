<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiConsumer
{
    /**
     * Allowed localhost addresses that bypass origin verification.
     *
     * @var array<int, string>
     */
    private const array LOCALHOST_ADDRESSES = [
        '127.0.0.1',
        '::1',
        'localhost',
    ];

    /**
     * Verify the request comes from an allowed API consumer.
     *
     * Checks the Origin and Referer headers against allowed origins,
     * and permits requests from localhost IPs. Requests without any
     * origin indicator (server-to-server with no headers) are blocked
     * unless from a local IP.
     *
     * @param  \Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isLocalRequest($request)) {
            return $next($request);
        }

        if ($this->isAllowedOrigin($request)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized API consumer.',
        ], 403);
    }

    private function isLocalRequest(Request $request): bool
    {
        return in_array($request->ip(), self::LOCALHOST_ADDRESSES);
    }

    private function isAllowedOrigin(Request $request): bool
    {
        $allowedOrigins = $this->getAllowedOrigins();
        $origin = $request->header('Origin') ?? $request->header('Referer');

        if ($origin === null) {
            return false;
        }

        foreach ($allowedOrigins as $allowed) {
            if ($allowed === '') {
                continue;
            }
            if ($allowed === null) {
                continue;
            }
            if (str_starts_with($origin, $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string|null>
     */
    private function getAllowedOrigins(): array
    {
        return [
            'http://localhost:5173',
            'http://localhost:5174',
            config('app.frontend_url'),
        ];
    }
}
