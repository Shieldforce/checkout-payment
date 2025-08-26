<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutStep4Methods extends Model
{
    protected $table = 'cpp_checkout_step_4_methods';

    protected $fillable = [
        'cpp_step_4_id',
        "method_type",
        "card_number",
        "card_token",
        "card_validate",
        "card_payer_name",
        "base_qrcode",
        "url_qrcode",
        "url_billet",
        "request_data",
        "response_data",
    ];

    protected $guarded = [];

    public function ccpStep4()
    {
        return $this->hasOne(
            CppCheckoutStep4::class,
            'id',
            'cpp_step_4_id',
        );
    }
}
