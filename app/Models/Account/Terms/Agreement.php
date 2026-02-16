<?php

namespace App\Models\Account\Terms;

use App\Traits\HasPublicId;
use Database\Factories\Account\Terms\AgreementFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Agreement extends Model
{
    use HasFactory, HasPublicId;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return AgreementFactory::new();
    }
}
