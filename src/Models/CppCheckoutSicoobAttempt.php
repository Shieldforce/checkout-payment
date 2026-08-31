<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutSicoobAttempt extends Model
{
    protected $table = 'cpp_checkout_sicoob_attempts';

    protected $fillable = [
        'cpp_checkout_id',
        'nosso_numero',
        'payment_method_id',
        'value',
        'due_date',
        'url_billet',
        'url_qrcode',
        'request_data',
        'response_data',
    ];

    protected $guarded = [];

    public function checkout()
    {
        return $this->belongsTo(
            CppCheckout::class,
            'cpp_checkout_id',
            'id',
        );
    }
}
