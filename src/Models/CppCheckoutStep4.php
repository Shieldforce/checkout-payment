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
