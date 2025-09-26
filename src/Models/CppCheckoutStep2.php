<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutStep2 extends Model
{
    protected $table = 'cpp_checkout_step_2';

    protected $fillable = [
        'cpp_checkout_id',
        'people_type',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'document',
        'visible',
    ];

    protected $guarded = [];

    public function ccpCheckout()
    {
        return $this->hasOne(
            CppCheckout::class,
            'id',
            'cpp_checkout_id',
        );
    }
}
