<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Shieldforce\CheckoutPayment\Observers\GatewayObserver;

class CppGateways extends Model
{
    protected $table = 'cpp_gateways';

    protected $fillable = [
        "name",
        "field_1",
        "field_2",
        "field_3",
        "field_4",
        "field_5",
        "field_6",
        "active",
    ];

    protected $guarded = [];

    protected $casts = [];

    protected static function boot()
    {
        parent::boot();

        self::observe(GatewayObserver::class);
    }
}
