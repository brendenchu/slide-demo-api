<?php

namespace App\Http\Middleware\Account;

use App\Services\AccountService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAreAccepted
{
    /**
     * Check if the user has accepted the terms of service.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accountService = (new AccountService())->setUser($request->user());

        // If the user has not accepted the terms, redirect them to the terms page.
        if (! $accountService->hasAcceptedTerms()) {
            return to_route('terms.setup');
        }

        return $next($request);
    }
}
