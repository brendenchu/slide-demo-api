<?php

namespace App\Models\Account\Terms;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    use HasPublicId;

    protected $table = 'account_terms_agreements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'accountable_id',
        'accountable_type',
        'terms_version_id',
        'accepted_at',
        'declined_at',
    ];

    public function accountable(): BelongsTo
    {
        return $this->morphTo();
    }
}
