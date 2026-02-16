<?php

namespace App\Http\Controllers\API\Account;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Account\AcceptTermsRequest;
use App\Models\Account\Terms\Agreement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermsController extends ApiController
{
    /**
     * Show current terms info and user acceptance status.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentVersion = config('terms.current_version');
        $versionConfig = config("terms.versions.{$currentVersion}", []);

        return $this->success([
            'version' => $currentVersion,
            'label' => $versionConfig['label'] ?? 'Terms of Service',
            'url' => $versionConfig['url'] ?? null,
            'accepted' => $user->hasAcceptedCurrentTerms(),
        ]);
    }

    /**
     * Accept the current terms of service.
     */
    public function accept(AcceptTermsRequest $request): JsonResponse
    {
        $user = $request->user();
        $currentVersion = config('terms.current_version');

        Agreement::query()->updateOrCreate(
            [
                'accountable_id' => $user->id,
                'accountable_type' => $user->getMorphClass(),
                'terms_version_id' => $currentVersion,
            ],
            [
                'accepted_at' => now(),
                'declined_at' => null,
            ]
        );

        return $this->success(message: 'Terms accepted successfully.');
    }
}
