<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySchedule extends Model
{
    protected $table = 'delivery_schedules';

    public $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('sort_order')->orderBy('id');
        });
    }

    public function charges(): HasMany
    {
        return $this->hasMany(DeliveryZoneScheduleCharge::class, 'delivery_schedule_id');
    }
}
