<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutStep3 extends Model
{
    protected $table = 'cpp_checkout_step_3';

    protected $fillable = [
        'cpp_checkout_id',
        "zipcode",
        "street",
        "district",
        "city",
        "state",
        "number",
        "complement",
    ];

    protected $guarded = [];

    public function ccpCheckout()
    {
        return $this->hasMany(
            CppCheckout::class,
            'id',
            'cpp_checkout_id',
        );
    }
}
