<?php

namespace App\Traits;

use App\Models\Account\Terms\Agreement;
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
