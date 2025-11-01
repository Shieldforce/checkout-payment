<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Ramsey\Uuid\Uuid;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;

class CppCheckout extends Model
{
    use Notifiable;

    protected $table = 'cpp_checkouts';

    protected $fillable = [
        'uuid',
        'cpp_gateway_id',
        'referencable_id',
        'referencable_type',
        'methods',
        'method_checked',
        'total_price',
        'due_date',
        'status',
        'return_gateway',
        'startOnStep',
        'url',
        'text_button_submit',
        'color_button_submit',
    ];

    protected $guarded = [];

    protected $casts = [
        'methods' => 'array',
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

        static::created(function (CppCheckout $checkout) {
            $checkout->update([
                'uuid' => Uuid::uuid3(
                    Uuid::NAMESPACE_DNS,
                    (string) $checkout->id
                )->toString(),
            ]);
        });
    }

    public function step1()
    {
        return $this->hasMany(
            CppCheckoutStep1::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function step2()
    {
        return $this->hasMany(
            CppCheckoutStep2::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function step3()
    {
        return $this->hasMany(
            CppCheckoutStep3::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function step4()
    {
        return $this->hasMany(
            CppCheckoutStep4::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function referencable()
    {
        return $this->morphTo();
    }
}
