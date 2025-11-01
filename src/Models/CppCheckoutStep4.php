<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutStep4 extends Model
{
    protected $table = 'cpp_checkout_step_4';

    protected $fillable = [
        'cpp_checkout_id',
        'card_number',
        'card_token',
        'installments',
        'payment_method_id',
        'card_validate',
        'card_payer_name',
        'base_qrcode',
        'url_qrcode',
        'url_billet',
        'visible',
        'request_credit_card_data',
        'response_credit_card_data',
        'request_pix_data',
        'response_pix_data',
        'request_billet_data',
        'response_billet_data',
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
