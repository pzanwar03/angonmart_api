<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $table = 'delivery_zones';

    public $guarded = [];

    protected $casts = [
        'charge' => 'float',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('updated_at', 'desc');
        });
    }

    public function areas(): HasMany
    {
        return $this->hasMany(DeliveryZoneArea::class, 'delivery_zone_id');
    }
}
