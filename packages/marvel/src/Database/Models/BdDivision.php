<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BdDivision extends Model
{
    protected $table = 'bd_divisions';

    public $guarded = [];

    public function districts(): HasMany
    {
        return $this->hasMany(BdDistrict::class, 'division_id');
    }
}
