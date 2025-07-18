<?php

namespace App\Http\Controllers\Account\Terms;

use App\Http\Controllers\Controller;
use App\Http\Resources\TermsResource;
use App\Services\AccountService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SetupTermsController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(AccountService $accountService): RedirectResponse|Response
    {
        $accountService->setUser(auth()->user());

        if ($accountService->hasAcceptedTerms()) {
            return to_route('story')->with('info', 'You have already accepted the terms.');
        }

        $terms = $accountService->setupTerms();

        return Inertia::render('Account/AcceptTerms', [
            'terms' => TermsResource::make($terms),
        ]);
    }
}
