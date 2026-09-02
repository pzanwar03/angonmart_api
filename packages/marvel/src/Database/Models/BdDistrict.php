<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BdDistrict extends Model
{
    protected $table = 'bd_districts';

    public $guarded = [];

    public function division(): BelongsTo
    {
        return $this->belongsTo(BdDivision::class, 'division_id');
    }

    public function thanas(): HasMany
    {
        return $this->hasMany(BdThana::class, 'district_id');
    }
}
