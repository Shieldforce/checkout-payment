<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;

class CppCheckout extends Model
{
    protected $table = 'cpp_checkouts';

    protected $fillable = [
        "cpp_gateway_id",
        "referencable_id",
        "referencable_type",
        "methods",
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

    protected $casts = [
        "methods" => "array",
    ];

    protected $attributes = [
        'methods' => '',
    ];

    public function initializeMethods(): void
    {
        if (empty($this->attributes['methods'])) {
            $this->attributes['methods'] = json_encode([
                MethodPaymentEnum::credit_card->value,
                MethodPaymentEnum::debit_card->value,
                MethodPaymentEnum::pix->value,
                MethodPaymentEnum::billet->value,
            ]);
        }
    }

    // hook de inicialização
    protected static function booted()
    {
        static::creating(function (CppCheckout $checkout) {
            $checkout->initializeMethods();
        });
    }

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
