<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'phone_number',
        'code',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
        'attempts'    => 'integer',
    ];
}
