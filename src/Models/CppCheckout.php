<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;

class CppCheckout extends Model
{
    protected $table = 'cpp_checkouts';

    protected $fillable = [
        "uuid",
        "cpp_gateway_id",
        "referencable_id",
        "referencable_type",
        "methods",
        "method_checked",
        "total_price",
        "status",
        "return_gateway",
        "startOnStep",
    ];

    protected $guarded = [];

    protected $casts = [
        "methods" => "array",
    ];

    protected $attributes = [
        'methods' => '',
    ];

    protected static function boot()
    {
        static::created(function (CppCheckout $checkout) {
            $checkout->uuid = Uuid::uuid3(
                Uuid::NAMESPACE_DNS,
                (string)$checkout->id
            )->toString();
        });
    }

    public function step1()
    {
        return $this->hasMany(
            CppCheckoutStep1::class,
            "cpp_checkout_id",
            "id",
        );
    }

    public function step2()
    {
        return $this->hasMany(
            CppCheckoutStep2::class,
            "cpp_checkout_id",
            "id",
        );
    }

    public function step3()
    {
        return $this->hasMany(
            CppCheckoutStep3::class,
            "cpp_checkout_id",
            "id",
        );
    }

    public function step4()
    {
        return $this->hasMany(
            CppCheckoutStep4::class,
            "cpp_checkout_id",
            "id",
        );
    }

    public function referencable()
    {
        return $this->morphTo();
    }
}
