<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckoutBillingLog extends Model
{
    protected $table = 'cpp_checkout_billing_logs';

    protected $fillable = [
        'cpp_checkout_id',
        'type',
        'action',
        'origin',
        'user_id',
        'value',
        'due_date',
        'mp_payment_id',
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

    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.users.model'),
            'user_id',
        );
    }
}
