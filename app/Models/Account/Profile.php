<?php

namespace App\Models\Account;

use App\Models\User;
use App\Traits\HasPublicId;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory, HasPublicId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'timezone',
        'locale',
        'currency',
    ];

    /**
     * The user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return ProfileFactory::new();
    }

    /**
     * The "booting" method of the model.
     */
    protected static function booted(): void
    {
        //
    }
}
