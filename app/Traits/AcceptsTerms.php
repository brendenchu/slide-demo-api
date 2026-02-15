<?php

namespace App\Traits;

use App\Models\Account\Terms\Agreement;
use App\Models\Account\Terms\Violation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @method morphMany(string $class, string $string)
 */
trait AcceptsTerms
{
    /**
     * The terms agreements that belong to the user.
     */
    public function terms_agreements(): MorphMany
    {
        return $this->morphMany(Agreement::class, 'accountable');
    }

    /**
     * The terms violations that belong to the user.
     */
    public function terms_violations(): MorphMany
    {
        return $this->morphMany(Violation::class, 'accountable');
    }

    /**
     * Determine if the user has accepted the current terms version.
     */
    public function hasAcceptedCurrentTerms(): bool
    {
        return $this->terms_agreements()
            ->where('terms_version_id', config('terms.current_version'))
            ->whereNotNull('accepted_at')
            ->exists();
    }
}
