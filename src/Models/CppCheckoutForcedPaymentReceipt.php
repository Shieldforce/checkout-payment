<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutForcedPaymentReceipt extends Model
{
    protected $table = 'cpp_checkout_forced_payment_receipts';

    protected $fillable = [
        'cpp_checkout_id',
        'path',
        'origin_name',
        'extension',
        'mime',
        'size',
    ];

    public function checkout()
    {
        return $this->belongsTo(
            CppCheckout::class,
            'cpp_checkout_id',
            'id',
        );
    }
}
