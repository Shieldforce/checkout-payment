<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutStep4 extends Model
{
    protected $table = 'cpp_checkout_step_4';

    protected $fillable = [
        'cpp_checkout_id',
        "card_number",
        "card_validate",
        "card_payer_name",
        "base_qrcode",
        "url_qrcode",
        "url_billet",
        'visible',
        'request_data',
        'response_data',
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

    public function methods()
    {
        return $this->hasMany(
            CppCheckoutStep4Methods::class,
            'cpp_step_4_id',
            'id',
        );
    }
}
