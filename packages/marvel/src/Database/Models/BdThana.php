<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BdThana extends Model
{
    protected $table = 'bd_thanas';

    public $guarded = [];

    public function district(): BelongsTo
    {
        return $this->belongsTo(BdDistrict::class, 'district_id');
    }
}
