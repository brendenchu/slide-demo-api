<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    protected $table = 'account_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'accountable_id',
        'accountable_type',
        'plan_id',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
        'canceled_by',
        'cancellation_reason',
        'status',
    ];

    /**
     * Get the subscription's plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
