<?php

namespace App\Models\Account\Terms;

use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    protected $table = 'account_violations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'accountable_id',
        'accountable_type',
        'terms_version_id',
        'reason',
    ];
}
