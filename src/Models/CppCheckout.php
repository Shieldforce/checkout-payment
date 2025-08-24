<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CppCheckout extends Model
{
    protected $table = 'cpp_checkouts';

    protected $fillable = [
        "cpp_gateway_id",
        "referencable_id",
        "referencable_type",
        "method",
        "first_name",
        "last_name",
        "email",
        "phone_number",
        "zip_code",
        "street",
        "district",
        "city",
        "state",
        "number",
        "complement",
        "document",
        "card_number",
        "card_validate",
        "card_payer_name",
        "total_price",
    ];

    protected $guarded = [];

    protected $casts = [];

    public function ccpItems()
    {
        return $this->hasMany(
            CppCheckoutItem::class,
            "cpp_checkout_id",
            "id",
        );
    }

    public function referencable()
    {
        return $this->morphTo();
    }
}
