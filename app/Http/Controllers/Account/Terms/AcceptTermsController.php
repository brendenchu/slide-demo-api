<?php

namespace App\Http\Controllers\Account\Terms;

use App\Http\Controllers\Controller;
use App\Models\Account\Terms\Agreement;
use App\Services\AccountService;
use Exception;
use Illuminate\Http\Request;

class AcceptTermsController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request, AccountService $accountService, Agreement $terms)
    {

        if (! $accountService
            ->setUser($request->user())
            ->acceptTerms($terms)) {
            return redirect()->back()->withErrors([
                'terms' => 'There was an error accepting the terms.',
            ]);
        }

        return to_route('story');
    }
}
