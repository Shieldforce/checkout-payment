<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutItem extends Model
{
    protected $table = 'cpp_checkout_items';

    protected $fillable = [
        'cpp_checkout_id',
        'referencable_type',
        'referencable_id',
        'name',
        'price',
        'price_2',
        'price_3',
        'description',
        'img',
    ];

    protected $guarded = [];

    protected $casts = [];

    public function ccpCheckout()
    {
        return $this->hasMany(
            CppCheckout::class,
            'id',
            'cpp_checkout_id',
        );
    }

    public function referencable()
    {
        return $this->morphTo();
    }
}
