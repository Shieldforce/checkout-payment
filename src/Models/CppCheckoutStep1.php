<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutStep1 extends Model
{
    protected $table = 'cpp_checkout_step_1';

    protected $fillable = [
        'cpp_checkout_id',
        'items',
    ];

    protected $guarded = [];

    protected $casts = [
        "items" => "array",
    ];

    public function ccpCheckout()
    {
        return $this->hasMany(
            CppCheckout::class,
            'id',
            'cpp_checkout_id',
        );
    }
}
