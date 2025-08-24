<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    protected $table = 'checkouts';

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
}
