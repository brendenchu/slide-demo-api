<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    /**
     * Handle an incoming request.
     *
     * Blocks access when the authenticated user has not accepted the current terms version.
     *
     * @param  \Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasAcceptedCurrentTerms()) {
            $currentVersion = config('terms.current_version');
            $versionConfig = config("terms.versions.{$currentVersion}", []);

            return response()->json([
                'success' => false,
                'message' => 'You must accept the current terms of service before continuing.',
                'must_accept_terms' => true,
                'terms' => [
                    'version' => $currentVersion,
                    'label' => $versionConfig['label'] ?? 'Terms of Service',
                    'url' => $versionConfig['url'] ?? null,
                ],
            ], 403);
        }

        return $next($request);
    }
}
